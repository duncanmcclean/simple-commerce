<?php

namespace Damcclean\Commerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AddedToCart
{
    use Dispatchable, InteractsWithSockets;

    public $product;

    public function __construct($product)
    {
        $this->product = $product;
    }
}
