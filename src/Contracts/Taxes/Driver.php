<?php

namespace DuncanMcClean\SimpleCommerce\Contracts\Taxes;

use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;
use DuncanMcClean\SimpleCommerce\Data\Address;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use Illuminate\Support\Collection;

interface Driver
{
    public function setAddress(Address $address): self;

    public function setPurchasable(Purchasable $purchasable): self;

    public function setLineItem(LineItem $lineItem): self;

    public function getBreakdown(int $total): Collection;
}