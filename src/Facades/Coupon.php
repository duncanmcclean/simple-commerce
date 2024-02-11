<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\CouponRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Contracts\CouponRepository
 */
class Coupon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CouponRepository::class;
    }
}
