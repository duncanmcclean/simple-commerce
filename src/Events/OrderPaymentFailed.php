<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class OrderPaymentFailed
{
    use Dispatchable;
    use InteractsWithSockets;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
