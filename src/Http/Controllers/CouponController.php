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

        $this->getCart()->calculateTotals();

        if (!$redeem) {
            return $this->withErrors($request, __('simple-commerce::messages.invalid_coupon'));
        }

        return $this->withSuccess($request, [
            'message' => __('simple-commerce::messages.coupon_added_to_cart'),
            'cart'    => $this->getCart()->toResource(),
        ]);
    }

    public function destroy(DestroyRequest $request)
    {
        $this->getCart()
            ->data([
                'coupon' => null,
            ])
            ->save()
            ->calculateTotals();

        return $this->withSuccess($request, [
            'message' => __('simple-commerce::messages.coupon_removed_from_cart'),
            'cart'    => $this->getCart()->toResource(),
        ]);
    }
}
