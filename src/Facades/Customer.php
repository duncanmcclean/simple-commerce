<?php

namespace Damcclean\Commerce\Facades;

use Damcclean\Commerce\Contracts\CustomerRepository;
use Illuminate\Support\Facades\Facade;

class Customer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CustomerRepository::class;
    }
}
