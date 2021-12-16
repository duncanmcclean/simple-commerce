<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxRate\TaxRateRepository;
use Illuminate\Support\Facades\Facade;

class TaxRate extends Facade
{
    protected static function getFacadeAccessor()
    {
        if (!SimpleCommerce::isUsingStandardTaxEngine()) {
            throw new \Exception("Sorry, the `TaxRate` facade is only available when using the 'Standard' tax engine.");
        }

        return new TaxRateRepository(app('stache'));
    }
}
