<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\Coupon\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Coupon\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;

class CouponController extends BaseActionController
{
    use CartDriver;

    public function store(StoreRequest $request)
    {
        $redeem = $this->getSessionCart()->redeemCoupon($request->code);

        if (! $redeem) {
            return $this->withErrors($request, __('simple-commerce::coupons.invalid_coupon'));
        }

        return $this->withSuccess($request, ['message' => __('simple-commerce::coupons.coupon_added_to_cart')]);
    }

    public function destroy(DestroyRequest $request)
    {
        $this->getSessionCart()
            ->update([
                'coupon' => null,
            ])
            ->calculateTotals();

        return $this->withSuccess($request, ['message' => __('simple-commerce::coupons.coupon_removed_from_cart')]);
    }
}
