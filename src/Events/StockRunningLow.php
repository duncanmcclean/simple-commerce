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

    public function __construct(Product $product, int $stock)
    {
        $this->product = $product;
        $this->stock = $stock;
    }
}
