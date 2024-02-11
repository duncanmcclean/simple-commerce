<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\OrderRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Contracts\OrderRepository
 */
class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrderRepository::class;
    }
}
