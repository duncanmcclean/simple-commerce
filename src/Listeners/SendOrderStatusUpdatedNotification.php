<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusUpdatedNotification
{
    public function handle(OrderStatusUpdated $event)
    {
        $event->customer->notify(new OrderStatusUpdated($event->order, $event->customer));
    }
}
