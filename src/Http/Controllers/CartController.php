<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        
        $cart = $cart->items([
            [
                'id' => Stache::generateId(),
                'product' => $request->product,
                'sku' => $request->sku,
                'quantity' => (int) $request->quantity,
                'total' => 0,
            ],
        ])->save();

        if (! Auth::guest()) {
            Cart::find($cart->id)->attachCustomer(User::current());
        }

        if (! $request->session()->has('simple-commerce-cart')) {
            $request->session()->put('simple-commerce-cart', $cart->id);
        }

        return back();
    }

    public function update()
    {
        //
    }

    public function destroy(Request $request, $id = null)
    {
        if (! $id) {
            // empty cart
        }

        // remove cart item wth id
    }
}