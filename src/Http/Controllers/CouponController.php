<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Coupon\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Coupon\StoreRequest;

class CouponController extends BaseActionController
{
    public function store(StoreRequest $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $redeem = Cart::find($request->session()->get(config('simple-commerce.cart_key')))
            ->redeemCoupon($request->code);

        if (!$redeem) {
            return $this->withErrors($request, __('simple-commerce::coupons.invalid_coupon'));
        }

        return $this->withSuccess($request, ['message' => __('simple-commerce::coupons.coupon_added_to_cart')]);
    }

    public function destroy(DestroyRequest $request)
    {
        $destroy = Cart::find($request->session()->get(config('simple-commerce.cart_key')))
            ->update([
                'coupon' => null,
            ])
            ->calculateTotals();

        return $this->withSuccess($request, ['message' => __('simple-commerce::coupons.coupon_removed_from_cart')]);
    }
}
