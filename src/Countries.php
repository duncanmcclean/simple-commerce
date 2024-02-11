<?php

namespace DuncanMcClean\SimpleCommerce;

use Illuminate\Support\Facades\File;

class Countries
{
    public static function __callStatic($method, $parameters)
    {
        return collect(json_decode(File::get(__DIR__.'/../resources/json/countries.json'), true))
            ->{$method}(...$parameters);
    }

    public static function find(string $id)
    {
        return static::firstWhere('iso', $id);
    }

    public static function findByRegion(array $region)
    {
        return static::where('iso', $region['country_iso']);
    }
}
