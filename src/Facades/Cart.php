<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\CartRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Contracts\Cart\CartRepository
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CartRepository::class;
    }
}
