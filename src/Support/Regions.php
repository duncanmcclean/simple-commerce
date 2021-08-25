<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use Illuminate\Support\Facades\File;

class Regions
{
    public static function __callStatic($method, $parameters)
    {
        return collect(json_decode(File::get(__DIR__.'/../../resources/json/regions.json'), true))
            ->{$method}(...$parameters);
    }

    public static function find(string $id)
    {
        return static::firstWhere('id', $id);
    }

    public static function findByCountry(array $country)
    {
        return static::where('country_iso', $country['iso']);
    }
}
