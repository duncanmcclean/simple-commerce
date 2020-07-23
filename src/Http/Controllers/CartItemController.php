<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Facades\Stache;

class CartItemController extends BaseActionController
{
    public function store(Request $request)
    {
        if (Session::has(config('simple-commerce.cart_key'))) {
            $cart = Cart::find(
                Session::get(config('simple-commerce.cart_key'))
            );
        } else {
            $cart = Cart::make()->save();
        }

        $cart->update([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $request->product,
                    'sku' => $request->sku,
                    'quantity' => (int) $request->quantity,
                    'total' => 0000,
                ],
            ],
        ])->calculateTotals();

        // dd($cart->entry());
        // dd($cart->entry());

        if (! Session::has(config('simple-commerce.cart_key'))) {
            Session::put(config('simple-commerce.cart_key'), $cart->id);
        }

        return $this->withSuccess($request);
    }

    public function update(Request $request, string $item)
    {
        $cart = Cart::find(Session::get(config('simple-commerce.cart_key')));

        $data = [];
        $item = collect($cart->data['items'])->where('id', $item)->first();

        $data['items'][] = array_merge(
            $item,
            Arr::except($request->all(), ['_token', '_params', '_redirect'])
        );

        $cart->update($data)
            ->calculateTotals();

        return $this->withSuccess($request);
    }

    public function destroy(Request $request, string $item)
    {
        $cart = Cart::find($request->session()->get(config('simple-commerce.cart_key')));

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
