<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use Illuminate\Support\Facades\Facade;

class Currency extends Facade
{
    public static function getFacadeAccessor()
    {
        return \DoubleThreeDigital\SimpleCommerce\Helpers\Currency::class;
    }
}
