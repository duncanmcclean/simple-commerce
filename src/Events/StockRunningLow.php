<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class StockRunningLow
{
    use Dispatchable;
    use InteractsWithSockets;

    public $product;
    public $stock;
    public $variant;

    // v2.4: Switch the parameter order - product, variant, stock
    public function __construct(Product $product, int $stock, $variant = null)
    {
        $this->product = $product;
        $this->stock = $stock;
        $this->variant = $variant;
    }
}
