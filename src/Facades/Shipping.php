<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingRepository;
use Illuminate\Support\Facades\Facade;

class Shipping extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ShippingRepository::class;
    }
}
