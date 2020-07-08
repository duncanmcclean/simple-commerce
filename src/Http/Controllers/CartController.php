<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CartController extends BaseActionController
{
    public function index()
    {
        //
    }

    public function update(Request $request)
    {
        Cart::find($request->session()->get('simple-commerce-cart'))
            ->update(Arr::except($request->all(), ['_token', '_params']))
            ->calculateTotals();

        return $this->withSuccess($request);
    }

    public function destroy(Request $request, string $item = null)
    {
        Cart::find($request->session()->get('simple-commerce-cart'))
            ->update([
                'items' => [],
            ])
            ->calculateTotals();

        return $this->withSuccess($request);
    }
}