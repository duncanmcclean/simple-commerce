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
        $redeem = $this->getCart()->redeemCoupon($request->code);

        $cart = $this->getCart();

        $cart->fresh();
        $cart->recalculate();

        if (! $redeem) {
            return $this->withErrors($request, __('simple-commerce::messages.invalid_coupon'));
        }

        return $this->withSuccess($request, [
            'message' => __('simple-commerce::messages.coupon_added_to_cart'),
            'cart'    => $this->getCart()->toResource(),
        ]);
    }

    public function destroy(DestroyRequest $request)
    {
        $cart = $this->getCart();

        $cart->coupon = null;

        $cart->save();

        $cart->recalculate();

        return $this->withSuccess($request, [
            'message' => __('simple-commerce::messages.coupon_removed_from_cart'),
            'cart'    => $this->getCart()->toResource(),
        ]);
    }
}
