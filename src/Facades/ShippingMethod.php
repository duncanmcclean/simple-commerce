<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Shipping\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Shipping\Manager
 */
class ShippingMethod extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
