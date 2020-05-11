<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Notifications\OrderRefunded as OrderRefundedNotification;

class SendOrderRefundedNotification
{
    public function handle(OrderRefunded $event)
    {
        $this->sendCustomerNotification($event->order);
    }

    protected function sendCustomerNotification(Order $order)
    {
        $order
            ->customer
            ->notify(new OrderRefundedNotification($order));
    }
}
