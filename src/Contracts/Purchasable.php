<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass;

interface Purchasable
{
    public function purchasablePrice(): int;

    public function purchasableTaxClass(): ?TaxClass;
}