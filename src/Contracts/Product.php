<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Statamic\Entries\Entry;

interface Product
{
    public function stockCount();

    public function purchasableType(): string;

    public function variantOption(string $optionKey): ?array;

    public function isExemptFromTax(): bool;
}
