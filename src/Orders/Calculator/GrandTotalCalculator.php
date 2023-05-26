<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;

class GrandTotalCalculator
{
    public function handle(OrderCalculation $orderCalculation, Closure $next)
    {
        $orderCalculation->order->grandTotal(
            (($orderCalculation->order->itemsTotal() + $orderCalculation->order->taxTotal()) - $orderCalculation->order->couponTotal()) + $orderCalculation->order->shippingTotal()
        );

        $orderCalculation->order->grandTotal(
            (int) $orderCalculation->order->grandTotal()
        );

        return $next($orderCalculation);
    }
}
