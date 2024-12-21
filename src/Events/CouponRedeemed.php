<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Coupons\Coupon;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

class CouponRedeemed
{
    public function __construct(public Coupon $coupon, public Order $order) {}
}
