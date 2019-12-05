<?php

namespace Damcclean\Commerce\Facades;

use Damcclean\Commerce\Contracts\CouponRepository;
use Illuminate\Support\Facades\Facade;

class Coupon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CouponRepository::class;
    }
}
