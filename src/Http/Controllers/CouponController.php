<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Http\Requests\Coupon\DestroyRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Coupon\StoreRequest;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;

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
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
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
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ]);
    }
}
