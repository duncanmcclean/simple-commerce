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
            return $this->withErrors($request, __('Coupon is not valid.'));
        }

        return $this->withSuccess($request, [
            'message' => __('Coupon added to cart'),
            'cart' => $this->getCart()->toAugmentedArray(),
        ]);
    }

    public function destroy(DestroyRequest $request)
    {
        $cart = $this->getCart();

        $cart->coupon = null;

        $cart->save();

        $cart->recalculate();

        return $this->withSuccess($request, [
            'message' => __('Coupon removed from cart'),
            'cart' => $this->getCart()->toAugmentedArray(),
        ]);
    }
}
