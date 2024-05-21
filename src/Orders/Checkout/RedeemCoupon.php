<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Checkout;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

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
