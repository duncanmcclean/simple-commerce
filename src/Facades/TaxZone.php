<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxZoneRepository as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Taxes\TaxZoneRepository
 */
class TaxZone extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
