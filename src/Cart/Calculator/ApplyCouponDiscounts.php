<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;

class ApplyCouponDiscounts
{
    public function handle(Cart $cart, Closure $next)
    {
        if ($coupon = $cart->coupon()) {
            $isValid = $cart->lineItems()->every(fn (LineItem $lineItem) => $coupon->isValid($cart, $lineItem));

            if (! $isValid) {
                $cart->coupon(null);

                return $next($cart);
            }

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

            $cart->discountTotal($cart->lineItems()->sum('discount_amount'));

            if ($cart->discountTotal() === 0) {
                $cart->coupon(null);
            }
        }

        return $next($cart);
    }
}
