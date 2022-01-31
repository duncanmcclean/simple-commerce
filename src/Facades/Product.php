<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array all()
 * @method static \Statamic\Entries\EntryCollection query()
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Product find(string $id)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\Product create(array $data = [], string $site = '')
 */
class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProductRepository::class;
    }
}
