<?php

namespace Damcclean\Commerce\Events;

use Damcclean\Commerce\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusUpdated
{
    use Dispatchable, InteractsWithSockets;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
