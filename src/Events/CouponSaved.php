<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Coupons\Coupon;

class CouponSaved
{
    public function __construct(public Coupon $coupon)
    {
    }
}