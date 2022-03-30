<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array all()
 * @method static \Statamic\Entries\EntryCollection query()
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Coupon find(string $id)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Coupon findByCode(string $code)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Coupon create(array $data = [], string $site = '')
 */
class Coupon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CouponRepository::class;
    }
}
