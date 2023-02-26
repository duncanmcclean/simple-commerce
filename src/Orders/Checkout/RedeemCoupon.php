<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Checkout;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;

class RedeemCoupon
{
    public function handle(Order $order, Closure $next)
    {
        if ($order->coupon()) {
            $order->coupon()->redeem();
        }

        return $next($order);
    }
}
