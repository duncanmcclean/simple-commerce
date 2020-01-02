<?php

namespace Damcclean\Commerce\Events;

use Damcclean\Commerce\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AddedToCart
{
    use Dispatchable, InteractsWithSockets;

    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;

        // TODO: add the product variant here too
    }
}
