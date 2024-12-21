<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait RedeemsCoupons
{
    public function redeemCoupon(Request $request, Cart $cart): Cart
    {
        if ($coupon = $request->coupon) {
            $coupon = Coupon::findByCode($coupon);

            if (! $coupon) {
                throw ValidationException::withMessages([
                    'coupon' => __("This coupon doesn't exist."),
                ]);
            }

            $isValid = $cart->lineItems()->every(fn (LineItem $lineItem) => $coupon->isValid($cart, $lineItem));

            if (! $isValid) {
                throw ValidationException::withMessages([
                    'coupon' => __("This coupon isn't valid for this cart."),
                ]);
            }

            $cart->coupon($coupon);
        }

        return $cart;
    }
}
