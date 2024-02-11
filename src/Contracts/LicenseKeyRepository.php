<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

/**
 * @mixin \DoubleThreeDigital\SimpleCommerce\Products\DigitalProducts\LicenseKeyRepository
 */
interface LicenseKeyRepository
{
    public function generate(): string;

    public static function bindings(): array;
}
