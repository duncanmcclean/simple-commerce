<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\TaxZoneRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Facade;

class TaxZone extends Facade
{
    protected static function getFacadeAccessor()
    {
        if (! SimpleCommerce::isUsingStandardTaxEngine()) {
            throw new \Exception("Sorry, the `TaxZone` facade is only available when using the 'Standard' tax engine.");
        }

        return TaxZoneRepository::class;
    }
}
