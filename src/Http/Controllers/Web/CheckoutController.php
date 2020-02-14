<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Events\NewCustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\ReturnCustomer;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantStockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency as CurrencyModel;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\StripeGateway;
use Illuminate\Http\Request;
use Statamic\Stache\Stache;
use Statamic\View\View;

class CheckoutController extends Controller
{
    public $cart;
    public $cartId;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    public function show(Request $request)
    {
        $this->createCart($request);

        if ($this->cart->total($this->cartId) == '0') {
            return (new View())
                ->template('commerce::web.checkout')
                ->layout('commerce::web.layout')
                ->with([
                    'title' => 'Checkout',
                ]);
        }

        $intent = (new StripeGateway())
            ->setupIntent($this->cart->total($this->cartId) * 100, (new Currency())->iso());

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
        // TODO: add a validation request here

        $this->createCart($request);

        $paymentMethod = (new StripeGateway())->completeIntent($request->payment_method);

        if ($customer = Customer::where('email', $request->email)->first()) {
            event(new ReturnCustomer($customer));
        } else {
            $customer = new Customer();
            $customer->uuid = (new Stache())->generateId();
            $customer->name = $request->name;
            $customer->email = $request->email;
            $customer->save();

            event(new NewCustomerCreated($customer));
        }

        $shippingAddress = new Address();
        $shippingAddress->uuid = (new Stache())->generateId();
        $shippingAddress->name = $customer->name;
        $shippingAddress->address1 = $request->shipping_address_1;
        $shippingAddress->address2 = $request->shipping_address_2;
        $shippingAddress->address3 = $request->shipping_address_3;
        $shippingAddress->city = $request->shipping_city;
        $shippingAddress->zip_code = $request->shipping_zip_code;
        $shippingAddress->country_id = Country::where('iso', $request->shipping_country)->first()->id;
        $shippingAddress->state_id = State::where('abbreviation', $request->shipping_state)->first()->id ?? null;
        $shippingAddress->customer_id = $customer->id;
        $shippingAddress->save();

        if ($request->use_shipping_address_for_billing === 'on') {
            $billingAddress = $shippingAddress;
        } else {
            $billingAddress = new Address();
            $billingAddress->uuid = (new Stache())->generateId();
            $billingAddress->name = $customer->name;
            $billingAddress->address1 = $request->billing_address_1;
            $billingAddress->address2 = $request->billing_address_2;
            $billingAddress->address3 = $request->billing_address_3;
            $billingAddress->city = $request->billing_city;
            $billingAddress->zip_code = $request->billing_zip_code;
            $billingAddress->country_id = Country::where('iso', $request->billing_country)->first()->id;
            $billingAddress->state_id = State::where('abbreviation', $request->billing_state)->first()->id ?? null;
            $billingAddress->customer_id = $customer->id;
            $billingAddress->save();
        }

        $order = new Order();
        $order->uuid = (new Stache())->generateId();
        $order->payment_intent = $request->payment_method;
        $order->billing_address_id = $billingAddress->id;
        $order->shipping_address_id = $shippingAddress->id;
        $order->customer_id = $customer->id;
        $order->order_status_id = OrderStatus::where('primary', true)->first()->id;
        $order->items = (new Cart())->orderItems($request->session()->get('commerce_cart_id'));
        $order->total = $this->cart->total($this->cartId);
        $order->currency_id = CurrencyModel::where('iso', config('simple-commerce.currency'))->first()->id;
        $order->save();

        event(new CheckoutComplete($order, $customer));

        collect($this->cart->get($this->cartId))
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

                // TODO: maybe this is the place where we could get all of the cart items and create an array for the orders table
            });

        $this->cart->clear($this->cartId);

        $request->session()->remove('commerce_cart_id');
        $this->createCart($request);

        return redirect(config('simple-commerce.checkout_redirect'));
    }

    protected function createCart(Request $request)
    {
        if (! $request->session()->get('commerce_cart_id')) {
            $request->session()->put('commerce_cart_id', $this->cart->create());
            $request->session()->save();
        }

        $this->cartId = $request->session()->get('commerce_cart_id');
    }
}
