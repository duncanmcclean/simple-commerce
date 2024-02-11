<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\TaxCategoryRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
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
