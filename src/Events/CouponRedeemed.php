<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CouponRedeemed
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Coupon $coupon)
    {
    }
}
