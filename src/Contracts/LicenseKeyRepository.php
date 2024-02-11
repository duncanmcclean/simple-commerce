<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

/**
 * @mixin \DuncanMcClean\SimpleCommerce\Products\DigitalProducts\LicenseKeyRepository
 */
interface LicenseKeyRepository
{
    public function generate(): string;

    public static function bindings(): array;
}
