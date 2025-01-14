<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Payments\Gateways\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Payments\Gateways\Manager
 */
class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
