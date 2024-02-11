<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\LicenseKeyRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DoubleThreeDigital\SimpleCommerce\Products\DigitalProducts\LicenseKeyRepository
 */
class LicenseKey extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LicenseKeyRepository::class;
    }
}
