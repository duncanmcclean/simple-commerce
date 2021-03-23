<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Support\Facades\Facade;

/**
 * We recommend using the `Order` facade instead. This facade will be removed in future versions.
 *
 * @deprecated
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Order::class;
    }
}
