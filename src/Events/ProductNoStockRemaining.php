<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;

class ProductNoStockRemaining
{
    public function __construct(public Purchasable $purchasable) {}
}
