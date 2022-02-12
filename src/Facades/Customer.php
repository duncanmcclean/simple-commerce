<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array all()
 * @method static \Statamic\Entries\EntryCollection query()
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Customer find(string $id)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Customer findByEmail(string $email)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Customer create(array $data = [], string $site = '')
 */
class Customer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CustomerRepository::class;
    }
}
