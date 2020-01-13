<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Events\NewCustomerCreated;
use Damcclean\Commerce\Events\VariantOutOfStock;
use Damcclean\Commerce\Events\VariantStockRunningLow;
use Damcclean\Commerce\Events\ReturnCustomer;
use Damcclean\Commerce\Helpers\Cart;
use Damcclean\Commerce\Helpers\Currency;
use Damcclean\Commerce\Models\Address;
use Damcclean\Commerce\Models\Customer;
use Damcclean\Commerce\Models\Order;
use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\Variant;
use Damcclean\Commerce\Tags\CartTags;
use Illuminate\Http\Request;
use Statamic\Stache\Stache;
use Statamic\View\View;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public $cartId;

    public function __construct()
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));

        $this->cart = new Cart();
    }

    public function show()
    {
        $this->createCart();

        if ($this->cart->total($this->cartId) == '0') {
            return (new View())
                ->template('commerce::web.checkout')
                ->layout('commerce::web.layout')
                ->with([
                    'title' => 'Checkout',
                ]);
        }

        $intent = PaymentIntent::create([
            'amount' => ($this->cart->total($this->cartId) * 100),
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
        $this->createCart();

        $paymentMethod = PaymentMethod::retrieve($request->payment_method);

        if ($customer = Customer::where('email', $request->email)->first()) {
            event(new ReturnCustomer($customer));
        } else {
            $customer = new Customer();
            $customer->uid = (new Stache())->generateId();
            $customer->name = $request->name;
            $customer->email = $request->email;
            $customer->save();

            event(new NewCustomerCreated($customer));
        }

        $billingAddress = new Address();
        $billingAddress->uid = (new Stache())->generateId();
        $billingAddress->name = $customer->name;
        $billingAddress->address1 = $request->billing_address_1;
        $billingAddress->address2 = $request->billing_address_2;
        $billingAddress->address3 = $request->billing_address_3;
        $billingAddress->city = $request->billing_city;
        $billingAddress->zip_code = $request->billing_zip_code;
        $billingAddress->country_id = $request->billing_country;
        $billingAddress->state_id = $request->billing_state;
        $billingAddress->customer_id = $customer->id;
        $billingAddress->save();

        if ($request->use_shipping_address_for_billing === 'on') {
            $shippingAddress = $billingAddress;
        } else {
            $shippingAddress = new Address();
            $shippingAddress->uid = (new Stache())->generateId();
            $shippingAddress->name = $customer->name;
            $shippingAddress->address1 = $request->shipping_address_1;
            $shippingAddress->address2 = $request->shipping_address_2;
            $shippingAddress->address3 = $request->shipping_address_3;
            $shippingAddress->city = $request->shipping_city;
            $shippingAddress->zip_code = $request->shipping_zip_code;
            $shippingAddress->country_id = $request->shipping_country;
            $shippingAddress->state_id = $request->shipping_state;
            $shippingAddress->customer_id = $customer->id;
            $shippingAddress->save();
        }

        $order = new Order();
        $order->uid = (new Stache())->generateId();
        $order->payment_intent = $request->payment_method;
        $order->billing_address_id = $billingAddress->id;
        $order->shipping_address_id = $shippingAddress->id;
        $order->customer_id = $customer->id;
        $order->order_status_id = 1; // TODO: use a configuration option for this
        $order->items = null; // TODO: work on this from the cart
        $order->total = $this->cart->total($this->cartId);
        $order->currency_id = Currency::where('iso', config('commerce.currency'))->first()->id;
        $order->save();

        event(new CheckoutComplete($order, $customer));

        collect($this->cart->get())
            ->each(function ($cartItem) {
                $product = Product::find($cartItem->product_id);

                $variant = Variant::find($cartItem->variant_id);
                $variant->stock -= $cartItem->quantity;
                $variant->save();

                if ($variant->stock === 0) {
                    event(new VariantOutOfStock($product, $variant));
                }

                if ($variant->stock <= 5) { // TODO: maybe make this configurable
                    event(new VariantStockRunningLow($product, $variant));
                }
            });

        $this->cart->clear($this->cartId);

        return redirect(config('commerce.routes.thanks'));
    }

    protected function createCart()
    {
        if (! request()->session()->get('commerce_cart_id')) {
            request()->session()->put('commerce_cart_id', $this->cart->create());
            request()->session()->save();
        }

        $this->cartId = request()->session()->get('commerce_cart_id');
    }
}
