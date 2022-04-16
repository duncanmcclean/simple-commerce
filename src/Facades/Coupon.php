<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository
 */
class Coupon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CouponRepository::class;
    }
}
