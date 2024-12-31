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
        if ($request->has('coupon')) {
            if (empty($request->coupon)) {
                $cart->coupon(null);

                return $cart;
            }

            $coupon = Coupon::findByCode($request->coupon);

            if (! $coupon) {
                throw ValidationException::withMessages([
                    'coupon' => __('Invalid coupon code.'),
                ]);
            }

            $isValid = $cart->lineItems()->every(fn (LineItem $lineItem) => $coupon->isValid($cart, $lineItem));

            if (! $isValid) {
                throw ValidationException::withMessages([
                    'coupon' => __('Invalid coupon code.'),
                ]);
            }

            $cart->coupon($coupon);
        }

        return $cart;
    }
}
