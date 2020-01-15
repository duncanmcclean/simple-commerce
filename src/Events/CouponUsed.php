<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CouponUsed
{
    use Dispatchable, InteractsWithSockets;

    public $coupon;

    public function __construct($coupon)
    {
        $this->coupon = $coupon;

        // TODO: implement the coupon model thing here (Coupon $coupon)
    }
}
