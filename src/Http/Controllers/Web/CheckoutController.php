<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Events\NewCustomerCreated;
use Damcclean\Commerce\Events\ProductOutOfStock;
use Damcclean\Commerce\Events\ProductStockRunningLow;
use Damcclean\Commerce\Events\ReturnCustomer;
use Damcclean\Commerce\Helpers\Cart;
use Damcclean\Commerce\Helpers\Currency;
use Damcclean\Commerce\Models\Customer;
use Damcclean\Commerce\Models\Order;
use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Tags\CartTags;
use Illuminate\Http\Request;
use Statamic\View\View;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));

        $this->cart = new Cart();

        if (! session()->get('commerce_cart_id')) {
            session()->put('commerce_cart_id', $this->cart->create());
        }

        $this->cartId = session()->get('commerce_cart_id');
    }

    public function show()
    {
        if ($this->cart->total($this->cartId) === '0') {
            return (new View())
                ->template('commerce::web.checkout')
                ->layout('commerce::web.layout')
                ->with([
                    'title' => 'Checkout',
                ]);
        }

        $intent = PaymentIntent::create([
            'amount' => (number_format($this->cart->total($this->cartId), 2, '.', '') * 100),
            'currency' => (new Currency())->primary()->iso,
        ]);

        return (new View)
            ->template('commerce::web.checkout')
            ->layout('commerce::web.layout')
            ->with([
                'title' => 'Checkout',
                'intent' => $intent->client_secret,
            ]);
    }

    public function store(Request $request)
    {
        $paymentMethod = PaymentMethod::retrieve($request->payment_method);

        if (isset(Customer::findByEmail($request->email)['id'])) {
            $customer = Customer::findByEmail($request->email);

            if ($paymentMethod->card->last4 != $customer['card_last_four']) {
                $customer = Customer::save(array_merge($customer, [
                    'card_brand' => $paymentMethod->card->brand,
                    'card_country' => $paymentMethod->card->country,
                    'card_expiry_month' => $paymentMethod->card->exp_month,
                    'card_expiry_year' => $paymentMethod->card->exp_year,
                    'card_last_four' => $paymentMethod->card->last4,
                ]));
            }

            event(new ReturnCustomer($customer));
        } else {
            $customer = Customer::save([
                'slug' => uniqid().'-'.str_slug($request->name),
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address ?? '',
                'country' => $request->country ?? '',
                'zip_code' => $request->zip_code ?? '',
                'card_brand' => $paymentMethod->card->brand,
                'card_country' => $paymentMethod->card->country,
                'card_expiry_month' => $paymentMethod->card->exp_month,
                'card_expiry_year' => $paymentMethod->card->exp_year,
                'card_last_four' => $paymentMethod->card->last4,
                'currency' => $request->currency ?? config('commerce.currency.code'),
                'customer_since' => now()->toDateTimeString(),
            ]);

            event(new NewCustomerCreated($customer));
        }

        $products = collect($this->cart->all())
            ->map(function ($cartProduct) {
                $product = Product::findBySlug($cartProduct['slug']);

                return [
                    'id' => $product['id'],
                    'quantity' => $cartProduct['quantity'],
                ];
            });

        $order = Order::save([
            'slug' => now()->year.'-'.now()->month.'-'.now()->day.'-'.mt_rand(),
            'total' => (new CartTags())->total(),
            'address' => $request->shipping_address ?? $request->address,
            'country' => $request->shipping_country ?? $request->country,
            'zip_code' => $request->shipping_zip_code ?? $request->zip_code,
            'status' => 'paid',
            'coupon' => null, // WIP when coupons happen
            'customer' => [collect($customer)->toArray()['id']],
            'order_date' => now()->toDateTimeString(),
            'products' => $products,
        ]);

        event(new CheckoutComplete($order, $customer));

        collect($this->cart->all())
            ->each(function ($cartProduct) {
                $product = Product::findBySlug($cartProduct['slug']);
                $product['stock_number'] -= $cartProduct['quantity'];

                Product::update($product['id'], $product);

                if ($product['stock_number'] == 0) {
                    event(new ProductOutOfStock($product));
                }

                if ($product['stock_number'] <= 5) {
                    event(new ProductStockRunningLow($product));
                }
            });

        $this->cart->clear($this->cartId);

        return redirect(config('commerce.routes.thanks'));
    }
}
