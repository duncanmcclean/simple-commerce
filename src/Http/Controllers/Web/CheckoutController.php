<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Events\VariantLowStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CheckoutRequest;
use DoubleThreeDigital\SimpleCommerce\Http\UsesCart;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Illuminate\Support\Facades\Event;
use Statamic\Stache\Stache;
use Statamic\View\View;

class CheckoutController extends Controller
{
    use UsesCart;

    public function show()
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
        $this->createCart();

        $payment = (new $request->gateway)->completePurchase($request->all());

        $customer = Customer::updateOrCreate(
            [
                'email' => $request->email,
            ],
            [
                'uuid'  => (new Stache())->generateId(),
                'name'  => $request->name,
                'email' => $request->email,
            ]
        );

        $billing = Address::updateOrCreate(
            [
                'customer_id'   => $request->customer_id,
                'address1'      => $request->shipping_address_1,
                'zip_code'      => $request->shipping_zip_code,
            ],
            [
                'uuid'          => (new Stache())->generateId(),
                'name'          => $customer->name,
                'address1'      => $request->shipping_address_1,
                'address2'      => $request->shipping_address_2,
                'address3'      => $request->shipping_address_3,
                'city'          => $request->shipping_city,
                'zip_code'      => $request->shipping_zip_code,
                'country_id'    => Country::where('iso', $request->shipping_country)->first()->id,
                'state_id'      => State::where('abbreviation', $request->shipping_state)->first()->id ?? null,
                'customer_id'   => $customer->id,
            ]
        );

        if ($request->use_shipping_address_for_billing === 'on') {
            $shipping = $billing;
        } else {
            $shipping = Address::updateOrCreate(
                [
                    'customer_id'   => $request->customer_id,
                    'address1'      => $request->shipping_address_1,
                    'zip_code'      => $request->shipping_zip_code,
                ],
                [
                    'uuid'          => (new Stache())->generateId(),
                    'name'          => $customer->name,
                    'address1'      => $request->shipping_address_1,
                    'address2'      => $request->shipping_address_2,
                    'address3'      => $request->shipping_address_3,
                    'city'          => $request->shipping_city,
                    'zip_code'      => $request->shipping_zip_code,
                    'country_id'    => Country::where('iso', $request->shipping_country)->first()->id,
                    'state_id'      => State::where('abbreviation', $request->shipping_state)->first()->id ?? null,
                    'customer_id'   => $customer->id,
                ]
            );
        }

        $order = Order::create([
            'uuid'                  => (new Stache())->generateId(),
            'billing_address_id'    => $billing->id,
            'shipping_address_id'   => $shipping->id,
            'customer_id'           => $customer->id,
            'order_status_id'       => OrderStatus::where('primary', true)->first()->id,
            'items'                 => $this->cart->orderItems($this->cartId),
            'total'                 => $this->cart->total($this->cartId),
            'currency_id'           => (new Currency())->primary()->id,
            'gateway_data'          => $payment,
            'is_paid'               => $payment['is_paid'],
            'is_refunded'           => false,
        ]);

        if ($payment['is_paid']) {
            Event::dispatch(new OrderPaid($order));
        }

        Event::dispatch(new OrderSuccessful($order));

        collect($this->cart->get($this->cartId))
            ->each(function (CartItem $cartItem) {
               $cartItem->variant()->update([
                   'stock' => ($cartItem->variant->stock - $cartItem->quantity),
               ]);

               if ($cartItem->variant->stock <= config('simple-commerce.low_stock_counter')) {
                   Event::dispatch(new VariantLowStock($cartItem->variant()));
               }

               if ($cartItem->variant->stock === 0) {
                   Event::dispatch(new VariantOutOfStock($cartItem->variant()));
               }
            });

        $this->replaceCart();

        return redirect(config('simple-commerce.routes.checkout_redirect'))
            ->with('success', 'Success! Your order has been placed. You should receive an email shortly.');
    }
}
