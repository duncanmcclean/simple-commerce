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
            ->redeemCoupon($request->code);

        if (! $redeem) {
            return $this->withErrors($request, ['Coupon is not valid.']);
        }

        return $this->withSuccess($request, ['message' => 'Coupon added to cart.']);
    }

    public function destroy(Request $request)
    {
        $destroy = Cart::find($request->session()->get(config('simple-commerce.cart_key')))
            ->update([
                'coupon' => null,
            ])
            ->calculateTotals();

        return $this->withSuccess($request, ['message' => 'Coupon removed from cart.']);
    }
}