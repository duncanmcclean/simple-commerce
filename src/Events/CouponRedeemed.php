<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CouponRedeemed
{
    use Dispatchable, InteractsWithSockets;

    public $coupon;
    public $order;

    public function __construct(Coupon $coupon, Order $order)
    {
        $this->coupon = $coupon;
        $this->order = $order;
    }
}
