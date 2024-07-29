<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Http\Requests\Coupon\DestroyRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Coupon\StoreRequest;

class CouponController extends BaseActionController
{
    public function store(StoreRequest $request)
    {
        $redeem = Cart::current()->redeemCoupon($request->code);

        $cart = Cart::current();

        $cart->fresh();
        $cart->recalculate();

        if (! $redeem) {
            return $this->withErrors($request, __('Coupon is not valid.'));
        }

        return $this->withSuccess($request, [
            'message' => __('Coupon added to cart'),
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ]);
    }

    public function destroy(DestroyRequest $request)
    {
        $cart = Cart::current();

        $cart->coupon = null;

        $cart->save();

        $cart->recalculate();

        return $this->withSuccess($request, [
            'message' => __('Coupon removed from cart'),
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ]);
    }
}
