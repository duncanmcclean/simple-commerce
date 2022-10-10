<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class StockRunningLow
{
    use Dispatchable;
    use InteractsWithSockets;

    // TODO v5.0: Switch the parameter order - product, variant, stock
    public function __construct(public Product $product, public int $stock, public $variant = null)
    {
    }
}
