<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Http\Request;

class CouponController extends BaseActionController
{
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $redeem = Cart::find($request->session()->get(config('simple-commerce.cart_key')))
            ->redeemCoupon($request->coupon);

        dd($redeem);

        // Return something...
    }

    public function destroy(Request $request)
    {
        //
    }
}