<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxCategory\TaxCategoryRepository;
use Illuminate\Support\Facades\Facade;

class TaxCategory extends Facade
{
    protected static function getFacadeAccessor()
    {
        if (! SimpleCommerce::isUsingStandardTaxEngine()) {
            throw new \Exception("Sorry, the `TaxCategory` facade is only available when using the 'Standard' tax engine.");
        }

        return new TaxCategoryRepository(app('stache'));
    }
}
