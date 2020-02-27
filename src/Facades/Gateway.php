<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use Illuminate\Support\Facades\Facade;

class Gateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \DoubleThreeDigital\SimpleCommerce\Helpers\Gateway::class;
    }
}
