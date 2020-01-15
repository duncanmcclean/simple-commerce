<?php

namespace Damcclean\Commerce\Listeners;

use Damcclean\Commerce\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusUpdatedNotification
{
    public function handle(OrderStatusUpdated $event)
    {
        Notification::route('mail', $event->customer->email)
            ->notify(new OrderStatusUpdated($event->order, $event->customer));
    }
}
