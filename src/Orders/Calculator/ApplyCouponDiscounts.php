<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;

class ApplyCouponDiscounts
{
    public function handle(Cart $cart, Closure $next)
    {
        if ($coupon = $cart->coupon()) {
            $cart->lineItems()->each(function (LineItem $lineItem) use ($cart, $coupon) {
                if (! $coupon->isValid($cart, $lineItem)) {
                    $lineItem->remove('discount_amount');
                    return;
                }

                $amount = (int) $coupon->amount();

                if ($coupon->type() === CouponType::Percentage) {
                    $lineItem->set('discount_amount', (int) ($amount * $lineItem->total()) / 100);
                }

                if ($coupon->type() === CouponType::Fixed) {
                    $lineItem->set('discount_amount', $amount);
                }
            });

            // TODO: Rename to discountTotal
            $cart->couponTotal($cart->lineItems()->sum('discount_amount'));

            if ($cart->couponTotal() === 0) {
                $cart->coupon(null);
            }
        }

        return $next($cart);
    }
}
