<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentStatusUpdated
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public Order $order, public PaymentStatus $paymentStatus)
    {
    }
}
