<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Notifications\OrderRefunded as OrderRefundedNotification;

class SendOrderRefundedNotification
{
    public function handle(OrderRefunded $event)
    {
        $event->order->customer->notify(new OrderRefundedNotification($event->order));
    }
}
