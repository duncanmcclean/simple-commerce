<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ProductUpdated
{
    use Dispatchable, InteractsWithSockets;

    public $product;

    /**
     * ProductUpdated constructor.
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}
