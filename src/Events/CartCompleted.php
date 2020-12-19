<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CartCompleted
{
    use Dispatchable, InteractsWithSockets;

    public Order $cart;

    public function __construct(Order $order)
    {
        $this->cart = $order;
    }
}
