<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use Illuminate\Support\Facades\Facade;

class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \DuncanMcClean\SimpleCommerce\Orders\Cart::class;
    }
}
