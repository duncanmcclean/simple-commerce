<?php

namespace Damcclean\Commerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CouponUsed
{
    use Dispatchable, InteractsWithSockets;

    public $coupon;

    public function __construct($coupon)
    {
        $this->coupon = $coupon;
    }
}
