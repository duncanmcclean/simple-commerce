<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;

class CalculateGrandTotal
{
    public function handle(Order $order, Closure $next)
    {
        $order->grandTotal(
            (($order->itemsTotal() + $order->taxTotal()) - $order->couponTotal()) + $order->shippingTotal()
        );

        $order->grandTotal(
            (int) $order->grandTotal()
        );

        return $next($order);
    }
}
