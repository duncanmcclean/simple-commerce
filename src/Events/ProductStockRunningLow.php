<?php

namespace Damcclean\Commerce\Events;

use Damcclean\Commerce\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ProductStockRunningLow
{
    use Dispatchable, InteractsWithSockets;

    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;

        // TODO: possibly change the name of this class to do with variants
        // TODO: pass in the variant
    }
}
