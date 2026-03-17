<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see CustomerRepository
 */
class Customer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CustomerRepository::class;
    }
}
