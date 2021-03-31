<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\ShippingManager use($className)
 */
class Shipping extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ShippingManager::class;
    }
}
