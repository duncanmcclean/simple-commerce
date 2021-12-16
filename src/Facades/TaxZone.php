<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxZone\TaxZoneRepository;
use Illuminate\Support\Facades\Facade;

class TaxZone extends Facade
{
    protected static function getFacadeAccessor()
    {
        if (!SimpleCommerce::isUsingStandardTaxEngine()) {
            throw new \Exception("Sorry, the `TaxZone` facade is only available when using the 'Standard' tax engine.");
        }

        return new TaxZoneRepository(app('stache'));
    }
}
