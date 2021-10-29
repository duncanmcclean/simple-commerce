<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\Currency as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array get(\Statamic\Sites\Site $site)
 * @method static string parse($amount, \Statamic\Sites\Site $site)
 * @method static int toPence(float $amount)
 * @method static float toDecimal(int $amount)
 */
class Currency extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
