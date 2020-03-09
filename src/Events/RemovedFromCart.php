<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RemovedFromCart
{
    use Dispatchable, InteractsWithSockets;

    public $cart;
    public $variant;

    public function __construct(Cart $cart, Variant $variant)
    {
        $this->cart = $cart;
        $this->variant = $variant;
    }
}
