<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PreCheckout
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Order $order, public $request) {}
}
