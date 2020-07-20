<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
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
        $data = Arr::except($request->all(), ['_token', '_params']);

        foreach ($data as $key => $value) {
            if ($value === 'on') {
                $value = true;
            } elseif ($value === 'off') {
                $value = false;
            }

            $data[$key] = $value;
        }

        Cart::find($request->session()->get(config('simple-commerce.cart_key')))
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
