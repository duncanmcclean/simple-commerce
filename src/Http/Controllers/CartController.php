<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CartController extends BaseActionController
{
    public function index(Request $request)
    {
        return Cart::find($request->session()->get(config('simple-commerce.cart_key')))
            ->entry()->data();
    }

    public function update(Request $request)
    {
        $cart = Cart::find($request->session()->get(config('simple-commerce.cart_key')));
        $data = Arr::except($request->all(), ['_token', '_params']);

        foreach ($data as $key => $value) {
            if ($value === 'on') {
                $value = true;
            } elseif ($value === 'off') {
                $value = false;
            }

            $data[$key] = $value;
        }

        if (isset($data['name']) && isset($data['email'])) {
            try {
                $customer = Customer::findByEmail($data['email']);
            } catch (CustomerNotFound $e) {
                $customer = Customer::make()
                    ->data([
                        'name' => $data['name'],
                        'email' => $data['email'],
                    ])
                    ->save();
            }

            $cart->update([
                'customer' => $customer->id,
            ]);

            unset($data['name']);
            unset($data['email']);
        }

        $cart
            ->update($data)
            ->calculateTotals();

        return $this->withSuccess($request);
    }

    public function destroy(Request $request)
    {
        Cart::find($request->session()->get(config('simple-commerce.cart_key')))
            ->update([
                'items' => [],
            ])
            ->calculateTotals();

        return $this->withSuccess($request);
    }
}
