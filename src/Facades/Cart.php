<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use Illuminate\Support\Facades\Facade;

class Cart extends Facade
{
    public static function getFacadeAccessor()
    {
        return \DoubleThreeDigital\SimpleCommerce\Helpers\Cart::class;
    }
}
