<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository
 */
class Customer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CustomerRepository::class;
    }
}
