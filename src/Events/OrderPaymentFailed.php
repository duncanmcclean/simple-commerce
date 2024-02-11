<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class OrderPaymentFailed
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Order $order)
    {
    }
}
