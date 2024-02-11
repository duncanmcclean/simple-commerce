<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusUpdated
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Order $order, public OrderStatus $orderStatus)
    {
    }
}
