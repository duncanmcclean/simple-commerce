<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array all()
 * @method static \Statamic\Entries\EntryCollection query()
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order find(string $id)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order create(array $data = [], string $site = '')
 */
class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
