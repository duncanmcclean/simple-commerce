<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartDestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartUpdateRequest;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function store(CartStoreRequest $request)
    {
        $this->dealWithSession();

        Cart::addLineItem(Session::get('simple_commerce_cart'), $request->variant, (int) $request->quantity, '');

        return $request->redirect ? redirect($request->redirect) : back();
    }

    public function update(CartUpdateRequest $request)
    {
        //

        return $request->redirect ? redirect($request->redirect) : back();
    }

    public function destroy(CartDestroyRequest $request)
    {
        //

        return $request->redirect ? redirect($request->redirect) : back();
    }

    protected function dealWithSession()
    {
        if (! Session::has('simple_commerce_cart')) {
            Session::put('simple_commerce_cart', Cart::make()->id);
        }
    }
}
