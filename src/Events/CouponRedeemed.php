<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Coupon;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CouponRedeemed
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Coupon $coupon, public Order $order)
    {
    }
}
