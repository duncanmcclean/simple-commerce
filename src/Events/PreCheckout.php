<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PreCheckout
{
    use Dispatchable;
    use InteractsWithSockets;

    public Order $order;
    public $request;

    public function __construct(Order $order, $request)
    {
        $this->order = $order;
        $this->request = $request;
    }
}
