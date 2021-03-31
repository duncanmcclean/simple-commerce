<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array all()
 * @method static \Statamic\Entries\EntryCollection query()
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order find(string $id)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order findByCode(string $code)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order create(array $data = [], string $site = '')
 */
class Coupon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
