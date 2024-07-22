<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingManager use($className)
 */
class Shipping extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ShippingManager::class;
    }
}
