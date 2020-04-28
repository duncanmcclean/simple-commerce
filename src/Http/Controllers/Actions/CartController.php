<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartDestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartUpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Illuminate\Support\Facades\Session;
use Statamic\Stache\Stache;

class CartController extends Controller
{
    public function store(CartStoreRequest $request)
    {
        $this->dealWithSession();

        Cart::addLineItem(
            Session::get(config('simple-commerce.cart_session_key')),
            $request->variant,
            (int) $request->quantity,
            $request->note ?? ''
        );

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function update(CartUpdateRequest $request)
    {
        $this->dealWithSession();

        if ($request->has('line_item') && $request->has('quantity')) {
            Cart::updateLineItem(
                Session::get(config('simple-commerce.cart_session_key')),
                $request->line_item,
                [
                    'quantity' => $request->quantity,
                ]
            );
        }

        if ($request->has('shipping_address_1') || $request->has('billing_address_1')) {
            $shipping = Address::create([
                'uuid'          => (new Stache())->generateId(),
                'name'          => $request->shipping_name,
                'address1'      => $request->shipping_address_1,
                'address2'      => $request->shipping_address_2,
                'address3'      => $request->shipping_address_3,
                'city'          => $request->shipping_city,
                'zip_code'      => $request->shipping_zip_code,
                'country_id'    => Country::where('iso', $request->shipping_country)->first()->id,
                'state_id'      => State::where('abbreviation', $request->shipping_state)->first()->id ?? null,
                'customer_id'   => null,
            ]);

            if ($request->use_shipping_address_for_billing === 'on') {
                $billing = $shipping;
            } else {
                $billing = Address::create([
                    'uuid'          => (new Stache())->generateId(),
                    'name'          => $request->billing_name,
                    'address1'      => $request->billing_address_1,
                    'address2'      => $request->billing_address_2,
                    'address3'      => $request->billing_address_3,
                    'city'          => $request->billing_city,
                    'zip_code'      => $request->billing_zip_code,
                    'country_id'    => Country::where('iso', $request->billing_country)->first()->id,
                    'state_id'      => State::where('abbreviation', $request->billing_state)->first()->id ?? null,
                    'customer_id'   => null,
                ]);
            }

            Cart::update(
                Session::get(config('simple-commerce.cart_session_key')),
                [
                    'billing_address_id'    => $billing->id,
                    'shipping_address_id'   => $shipping->id,
                ]
            );

            $order = Order::where('uuid', Session::get(config('simple-commerce.cart_session_key')))->first();
            Cart::decideShipping($order);
            Cart::calculateTotals($order);
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function destroy(CartDestroyRequest $request)
    {
        $this->dealWithSession();

        if ($request->has('clear')) {
            Cart::clear(Session::get(config('simple-commerce.cart_session_key')));
            Session::remove(config('simple-commerce.cart_session_key'));

            $this->dealWithSession();
        }

        if ($request->has('line_item')) {
            Cart::removeLineItem(
                Session::get(config('simple-commerce.cart_session_key')),
                $request->line_item
            );
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    protected function dealWithSession()
    {
        if (! Session::has(config('simple-commerce.cart_session_key'))) {
            Session::put(config('simple-commerce.cart_session_key'), Cart::make()->uuid);
        }
    }
}
