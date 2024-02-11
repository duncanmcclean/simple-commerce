<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\ShippingManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\ShippingManager use($className)
 */
class Shipping extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ShippingManager::class;
    }
}
