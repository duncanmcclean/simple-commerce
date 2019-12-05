<?php

namespace Damcclean\Commerce\Facades;

use Damcclean\Commerce\Contracts\CouponRepository;

class Coupon
{
    protected static function getFacadeAccessor()
    {
        return CouponRepository::class;
    }
}
