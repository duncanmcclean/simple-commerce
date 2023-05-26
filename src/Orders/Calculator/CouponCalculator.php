<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Coupons\CouponType;

class CouponCalculator
{
    public function handle(OrderCalculation $orderCalculation, Closure $next)
    {
        if ($coupon = $orderCalculation->order->coupon()) {
            $value = (int) $coupon->value();

            // Double check coupon is still valid
            if (! $coupon->isValid($orderCalculation->order)) {
                return $next($orderCalculation);
            }

            $baseAmount = $orderCalculation->order->itemsTotal() + $orderCalculation->order->taxTotal();

            // Otherwise do all the other stuff...
            if ($coupon->type() === CouponType::Percentage) {
                $orderCalculation->order->couponTotal(
                    (int) ($value * $baseAmount) / 100
                );
            }

            if ($coupon->type() === CouponType::Fixed) {
                $orderCalculation->order->couponTotal(
                    (int) $baseAmount - ($baseAmount - $value)
                );
            }

            $orderCalculation->order->couponTotal(
                (int) round($orderCalculation->order->couponTotal())
            );
        }

        return $next($orderCalculation);
    }
}
