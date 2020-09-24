<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class StockRunningLow
{
    use Dispatchable, InteractsWithSockets;

    public $product;
    public $stock;

    public function __construct(ProductRepository $product, int $stock)
    {
        $this->product = $product;
        $this->stock = $stock;
    }
}
