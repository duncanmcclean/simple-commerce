<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Notifications\OrderRefunded as OrderRefundedNotification;
use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;

class SendOrderRefundedNotification
{
    public function handle(OrderRefunded $event)
    {
        $event->order->customer->notify(new OrderRefundedNotification($event->order));
    }
}
