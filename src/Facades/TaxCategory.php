<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\TaxCategoryRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Facade;

class TaxCategory extends Facade
{
    protected static function getFacadeAccessor()
    {
        if (! SimpleCommerce::isUsingStandardTaxEngine()) {
            throw new \Exception("Sorry, the `TaxCategory` facade is only available when using the 'Standard' tax engine.");
        }

        return TaxCategoryRepository::class;
    }
}
