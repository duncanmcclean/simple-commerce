<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RedeemCouponController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->dealWithSession();

        Cart::redeemCoupon(
            Session::get(config('simple-commerce.cart_session_key')),
            $request->coupon
        );

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    protected function dealWithSession()
    {
        if (!Session::has(config('simple-commerce.cart_session_key'))) {
            Session::put(config('simple-commerce.cart_session_key'), Cart::make()->uuid);
        }
    }
}
