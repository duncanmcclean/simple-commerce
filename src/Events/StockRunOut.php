<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class StockRunOut
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Product $product, public $variant, public int $stock)
    {
    }
}
