<?php

namespace DuncanMcClean\SimpleCommerce\Products\DigitalProducts;

use DuncanMcClean\SimpleCommerce\Contracts\LicenseKeyRepository as Contract;

class LicenseKeyRepository implements Contract
{
    protected int $length = 24;

    protected string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function generate(): string
    {
        $key = '';

        for ($i = 0; $i < $this->length; $i++) {
            $key .= $this->characters[random_int(0, strlen($this->characters) - 1)];
        }

        return $key;
    }

    public static function bindings(): array
    {
        return [];
    }
}
