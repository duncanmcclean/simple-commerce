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

        Cart::addLineItem(
            Session::get(config('simple-commerce.cart_session_key')),
            $request->variant,
            (int) $request->quantity,
            $request->note ?? ''
        );

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
        if (! Session::has(config('simple-commerce.cart_session_key'))) {
            Session::put(config('simple-commerce.cart_session_key'), Cart::make()->uuid);
        }
    }
}
