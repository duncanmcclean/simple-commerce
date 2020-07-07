<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\Stache;
use Statamic\Facades\User;

class CartItemController extends BaseActionController
{
    public function store(Request $request)
    {   
        if ($request->session()->has('simple-commerce-cart')) {
            $cart = Cart::find($request->session()->get('simple-commerce-cart'));
        } else {
            $cart = Cart::make();
        }
        
        $cart = $cart->items([
            [
                'id' => Stache::generateId(),
                'product' => $request->product,
                'sku' => $request->sku,
                'quantity' => (int) $request->quantity,
                'total' => 0000,
            ],
        ])->save()->calculateTotals();

        if (! Auth::guest()) {
            Cart::find($cart->id)->attachCustomer(User::current());
        }

        if (! $request->session()->has('simple-commerce-cart')) {
            $request->session()->put('simple-commerce-cart', $cart->id);
        }

        return $this->withSuccess($request);
    }

    public function update(Request $request, string $item = null)
    {
        $cart = Cart::find($request->session()->get('simple-commerce-cart'));

        if (! Auth::guest()) {
            $cart->attachCustomer(User::current());
        }

        $data = [];

        if ($item) {
            $data['items'][] = array_merge(
                collect($cart->entry()->get('items'))->where('id', $item)->first(),
                Arr::except($request->all(), ['_token', '_params'])
            );
        } else {
            $data = Arr::except($request->all(), ['_token', '_params']);
        }

        $cart = $cart->update($data);

        $cart->calculateTotals();

        return $this->withSuccess($request);
    }

    public function destroy(Request $request, string $item = null)
    {
        $cart = Cart::find($request->session()->get('simple-commerce-cart'));

        $cart->update([
            'items' => collect($cart->items)
                ->reject(function ($item) {
                    return $item['id'] != $item;
                })
                ->toArray()
        ]);
        
        $cart->calculateTotals();

        return $this->withSuccess($request);
    }
}