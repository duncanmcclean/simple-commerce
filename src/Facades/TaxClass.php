<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClassRepository as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Taxes\TaxClassRepository
 */
class TaxClass extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
