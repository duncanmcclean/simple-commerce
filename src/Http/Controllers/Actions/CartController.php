<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartDestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartUpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Support\Facades\Session;
use test\Mockery\MockingVariadicArgumentsTest;

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
        // update line item's quantity

        Cart::updateLineItem(
            Session::get(config('simple-commerce.cart_session_key')),
            $request->line_item,
            [
                'quantity' => $request->quantity,
            ]
        );

        return $request->redirect ? redirect($request->redirect) : back();
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

        return $request->redirect ? redirect($request->redirect) : back();
    }

    protected function dealWithSession()
    {
        if (! Session::has(config('simple-commerce.cart_session_key'))) {
            Session::put(config('simple-commerce.cart_session_key'), Cart::make()->uuid);
        }
    }
}
