<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository
 */
class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProductRepository::class;
    }
}
