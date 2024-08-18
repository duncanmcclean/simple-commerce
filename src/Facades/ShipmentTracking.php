<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\ShipmentTrackingRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Contracts\CouponRepository
 */
class ShipmentTracking extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ShipmentTrackingRepository::class;
    }
}
