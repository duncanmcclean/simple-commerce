<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CartCreated
{
    use Dispatchable, InteractsWithSockets;

    public $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
}
