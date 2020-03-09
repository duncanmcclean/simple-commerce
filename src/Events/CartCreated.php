<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

class CartCreated
{
    use Dispatchable, InteractsWithSockets;

    public $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
}
