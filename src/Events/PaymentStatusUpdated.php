<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
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
