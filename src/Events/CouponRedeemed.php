<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Entries\Entry;

class CouponRedeemed
{
    use Dispatchable;
    use InteractsWithSockets;

    public $coupon;

    public function __construct(Entry $coupon)
    {
        $this->coupon = $coupon;
    }
}
