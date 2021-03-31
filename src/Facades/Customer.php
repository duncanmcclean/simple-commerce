<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array all()
 * @method static \Statamic\Entries\EntryCollection query()
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order find(string $id)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order findByEmail(string $email)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Order create(array $data = [], string $site = '')
 */
class Customer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
