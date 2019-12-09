<?php

namespace Damcclean\Commerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ProductOutOfStock
{
    use Dispatchable, InteractsWithSockets;

    public $product;

    public function __construct($product)
    {
        $this->product = $product;
    }
}
