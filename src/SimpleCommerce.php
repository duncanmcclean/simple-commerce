<?php

namespace DuncanMcClean\SimpleCommerce;

use Illuminate\Support\Facades\File;
use Statamic\Facades\Addon;
use Statamic\Statamic;

class SimpleCommerce
{
    public static function version(): string
    {
        if (app()->environment('testing')) {
            return 'v8.0.0';
        }

        return Addon::get('duncanmcclean/simple-commerce')->version();
    }

    /**
     * This shouldn't be used as a Statamic::svg() replacement. It's only useful for grabbing
     * icons from Simple Commerce's `resources/svgs` directory.
     */
    public static function svg($name)
    {
        if (File::exists(__DIR__.'/../resources/svg/'.$name.'.svg')) {
            return File::get(__DIR__.'/../resources/svg/'.$name.'.svg');
        }

        return Statamic::svg($name);
    }
}
