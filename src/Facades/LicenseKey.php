<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\LicenseKeyRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\SimpleCommerce\Products\DigitalProducts\LicenseKeyRepository
 */
class LicenseKey extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LicenseKeyRepository::class;
    }
}
