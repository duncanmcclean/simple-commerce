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

        $this->getCart()->recalculate();

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
        // TODO: We need to figure out a way of making this work with different drivers (eg. Eloquent uses coupon_id instead of coupon)
        $this->getCart()
            ->set('coupon', null)
            ->save()
            ->recalculate();

        return $this->withSuccess($request, [
            'message' => __('simple-commerce::messages.coupon_removed_from_cart'),
            'cart'    => $this->getCart()->toResource(),
        ]);
    }
}
