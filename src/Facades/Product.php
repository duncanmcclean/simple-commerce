<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\ProductRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see ProductRepository
 */
class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProductRepository::class;
    }
}
