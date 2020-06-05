<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusUpdated
{
    use Dispatchable, InteractsWithSockets;

    public $order;
    public $orderStatus;

    /**
     * OrderStatusUpdated constructor.
     * @param Order $order
     * @param OrderStatus $orderStatus
     */
    public function __construct(Order $order, OrderStatus $orderStatus)
    {
        $this->order = $order;
        $this->orderStatus = $orderStatus;
    }
}
