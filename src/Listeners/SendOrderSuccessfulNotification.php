<?php

namespace Damcclean\Commerce\Listeners;

use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Notifications\OrderSuccessful;
use Illuminate\Support\Facades\Notification;

class SendOrderSuccessfulNotification
{
    public function handle(CheckoutComplete $event)
    {
        Notification::route('mail', $event->customer->name)
            ->notify(new OrderSuccessful($event->order, $event->customer));
    }
}
