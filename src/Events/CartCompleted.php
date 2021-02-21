<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CartCompleted
{
    use Dispatchable;
    use InteractsWithSockets;

    public Order $cart;
    public Order $order;

    public function __construct(Order $order)
    {
        // TODO: get rid of $cart
        $this->cart = $order;

        $this->order = $order;
    }
}
