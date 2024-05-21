<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository
 */
class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrderRepository::class;
    }
}
