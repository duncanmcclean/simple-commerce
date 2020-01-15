<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VariantOutOfStock
{
    use Dispatchable, InteractsWithSockets;

    public $product;
    public $variant;

    public function __construct(Product $product, Variant $variant)
    {
        $this->product = $product;
        $this->variant = $variant;
    }
}
