<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product;
use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;

class ProductStockLow
{
    public function __construct(public Purchasable $purchasable) {}
}
