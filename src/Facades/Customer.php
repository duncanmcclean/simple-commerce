<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\Customers\CustomerRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Contracts\Customers\CustomerRepository
 */
class Customer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CustomerRepository::class;
    }
}
