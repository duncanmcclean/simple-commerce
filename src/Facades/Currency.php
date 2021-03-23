<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Currency as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array get(\Statamic\Sites\Site $site)
 * @method static string parse($price, \Statamic\Sites\Site $site)
 */
class Currency extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
