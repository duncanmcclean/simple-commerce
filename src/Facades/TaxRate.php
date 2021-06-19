<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxRate\TaxRateRepository;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxEngine as StandardTaxEngine;
use Illuminate\Support\Facades\Facade;

class TaxRate extends Facade
{
    protected static function getFacadeAccessor()
    {
        if (! SimpleCommerce::taxEngine() instanceof StandardTaxEngine) {
            throw new \Exception("This facade is only available when using the 'Standard' tax engine.");
        }

        return new TaxRateRepository(app('stache'));
    }
}
