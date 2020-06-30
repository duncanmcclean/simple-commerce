<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\User;

class CartController extends BaseActionController
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {   
        if ($request->session()->has('simple-commerce-cart')) {
            $cart = Cart::find($request->session()->get('simple-commerce-cart'));
        } else {
            $cart = Cart::make();
        }

        $product = Entry::find($request->product);
        
        $cart = $cart->items([
            [
                'id' => Stache::generateId(),
                'product' => $request->product,
                'sku' => $request->sku,
                'quantity' => (int) $request->quantity,
                'total' => (int) $product->data()->get('price'),
            ],
        ])->save();

        if (! Auth::guest()) {
            Cart::find($cart->id)->attachCustomer(User::current());
        }

        if (! $request->session()->has('simple-commerce-cart')) {
            $request->session()->put('simple-commerce-cart', $cart->id);
        }

        return $this->withSuccess($request);
    }

    public function update()
    {
        //
    }

    public function destroy(Request $request, string $item = null)
    {
        $cart = Cart::find($request->session()->get('simple-commerce-cart'));

        if (! $item) {
            $cart->update([
                'items' => []
            ]);
        }

        $cart->update([
            'items' => collect($cart->items)
                ->reject(function ($item) {
                    return $item['id'] != $item;
                })
                ->toArray()
        ]);

        return $this->withSuccess($request);
    }
}