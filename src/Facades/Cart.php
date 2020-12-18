<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Support\Facades\Facade;

class Cart extends Facade
{
    /**
     * We recommend using the `Order` facade instead. This facade will be removed in future versions.
     *
     * @deprecated
     */
    protected static function getFacadeAccessor()
    {
        return Order::class;
    }
}
