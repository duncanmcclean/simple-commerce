<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as Contract;
use Illuminate\Support\Facades\Facade;

class Customer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
