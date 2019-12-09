<?php

namespace Damcclean\Commerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusUpdated
{
    use Dispatchable, InteractsWithSockets;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }
}
