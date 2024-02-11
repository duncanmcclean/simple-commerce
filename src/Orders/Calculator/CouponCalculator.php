<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Coupons\CouponType;

class CouponCalculator
{
    public function handle(Order $order, Closure $next)
    {
        if ($coupon = $order->coupon()) {
            $value = (int) $coupon->value();

            // Double check coupon is still valid
            if (! $coupon->isValid($order)) {
                return $next($order);
            }

            $baseAmount = $order->itemsTotal() + $order->taxTotal();

            // Otherwise do all the other stuff...
            if ($coupon->type() === CouponType::Percentage) {
                $order->couponTotal(
                    (int) ($value * $baseAmount) / 100
                );
            }

            if ($coupon->type() === CouponType::Fixed) {
                $order->couponTotal(
                    (int) $baseAmount - ($baseAmount - $value)
                );
            }

            $order->couponTotal(
                (int) round($order->couponTotal())
            );
        }

        return $next($order);
    }
}
