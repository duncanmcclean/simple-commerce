<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use Illuminate\Support\Facades\File;

class Regions
{
    public static function __callStatic($method, $parameters)
    {
        return collect(json_decode(File::get(__DIR__.'/../../resources/json/regions.json')))
            ->{$method}(...$parameters);
    }
}
