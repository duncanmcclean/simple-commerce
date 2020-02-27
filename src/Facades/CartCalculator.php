<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use Illuminate\Support\Facades\Facade;

class CartCalculator extends Facade
{
    public static function getFacadeAccessor()
    {
        return \DoubleThreeDigital\SimpleCommerce\Helpers\CartCalculator::class;
    }
}
