<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PreCheckout
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Order $order, public $request)
    {
    }
}
