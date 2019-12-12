<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Events\NewCustomerCreated;
use Damcclean\Commerce\Events\ProductOutOfStock;
use Damcclean\Commerce\Events\ProductStockRunningLow;
use Damcclean\Commerce\Events\ReturnCustomer;
use Damcclean\Commerce\Facades\Customer;
use Damcclean\Commerce\Facades\Order;
use Damcclean\Commerce\Facades\Product;
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
    }

    public function show()
    {
        $intent = PaymentIntent::create([
            'amount' => (new CartTags())->total() * 100,
            'currency' => config('commerce.currency.code'),
        ]);

        return (new View)
            ->template('commerce::web.checkout')
            ->layout('commerce::web.layout')
            ->with([
                'intent' => $intent->client_secret
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
            ]);

            event(new NewCustomerCreated($customer));
        }

        $order = Order::save([
            'slug' => uniqid(),
            'total' => (new CartTags())->total(),
            'address' => $request->shipping_address ?? $request->address,
            'country' => $request->shipping_country ?? $request->country,
            'zip_code' => $request->shipping_zip_code ?? $request->zip_code,
            'status' => 'paid',
            'coupon' => null, // WIP when coupons happen
            'customer' => collect($customer)->toArray()['id'],
            'order_date' => now()->toDateTimeString()
        ]);

        event(new CheckoutComplete($order, $customer));

        collect($request->session()->get('cart'))
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

        $request->session()->forget('cart');

        return redirect('/thanks');
    }
}
