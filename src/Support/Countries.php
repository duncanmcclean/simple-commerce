<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use Illuminate\Support\Facades\File;

class Countries
{
    public static function __callStatic($method, $parameters)
    {
        return collect(json_decode(File::get(__DIR__.'/../../resources/json/countries.json')))
            ->{$method}(...$parameters);
    }

    public static function findByRegion(array $region)
    {
        return static::firstWhere('iso', $region['country_iso']);
    }
}
