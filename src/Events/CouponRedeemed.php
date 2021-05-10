<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CouponRedeemed
{
    use Dispatchable;
    use InteractsWithSockets;

    public $coupon;

    public function __construct(Coupon $coupon)
    {
        $this->coupon = $coupon;
    }
}
