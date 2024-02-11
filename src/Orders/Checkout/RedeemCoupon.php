<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Checkout;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;

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
