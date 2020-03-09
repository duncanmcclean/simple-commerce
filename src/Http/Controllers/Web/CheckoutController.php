<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CheckoutRequest;
use DoubleThreeDigital\SimpleCommerce\Http\UsesCart;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency as CurrencyModel;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Http\Request;
use Statamic\Stache\Stache;
use Statamic\View\View;

class CheckoutController extends Controller
{
    use UsesCart;

    public function show(Request $request)
    {
        $this->createCart();

        return (new View)
            ->template('simple-commerce::web.checkout')
            ->layout('simple-commerce::web.layout')
            ->with([
                'title' => 'Checkout',
            ]);
    }

    public function store(CheckoutRequest $request)
    {
        // TODO: this method needs refactoring big time!!

        $this->createCart();

        $payment = (new $request->gateway)->completePurchase($request->all());

        if (! $customer = Customer::where('email', $request->email)->first()) {
            $customer = new Customer();
            $customer->uuid = (new Stache())->generateId(); // TODO: this should not be required if using the uuid trait
            $customer->name = $request->name;
            $customer->email = $request->email;
            $customer->save();
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
        $order->gateway_data = array_merge($payment, [
            'gateway' => $request->gateway,
        ]);
        $order->billing_address_id = $billingAddress->id;
        $order->shipping_address_id = $shippingAddress->id;
        $order->customer_id = $customer->id;
        $order->order_status_id = OrderStatus::where('primary', true)->first()->id;
        $order->items = (new Cart())->orderItems($request->session()->get('commerce_cart_id'));
        $order->total = $this->cart->total($this->cartId);
        $order->currency_id = CurrencyModel::where('iso', config('simple-commerce.currency.iso'))->first()->id;
        $order->is_paid = $payment['is_paid'];
        $order->is_refunded = false;
        $order->save();

        collect($this->cart->get($this->cartId))
            ->each(function ($cartItem) {
                $product = Product::find($cartItem->product_id);

                $variant = Variant::find($cartItem->variant_id);
                $variant->stock -= $cartItem->quantity;
                $variant->save();

                if ($variant->stock === 0) {
//                    event(new VariantOutOfStock($product, $variant));
                }

                if ($variant->stock <= 5) { // TODO: maybe make this configurable
//                    event(new VariantStockRunningLow($product, $variant));
                }
            });

        $this->cart->clear($this->cartId);

        $request->session()->remove('commerce_cart_id');
        $this->createCart($request);

        return redirect(config('simple-commerce.routes.checkout_redirect'))
            ->with('success', 'Success! Your order has been placed. You should receive an email shortly.');
    }
}
