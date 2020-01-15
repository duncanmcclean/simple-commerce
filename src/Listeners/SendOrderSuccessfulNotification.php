<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Notifications\OrderSuccessful;
use Illuminate\Support\Facades\Notification;

class SendOrderSuccessfulNotification
{
    public function handle(CheckoutComplete $event)
    {
        Notification::route('mail', $event->customer->name)
            ->notify(new OrderSuccessful($event->order, $event->customer));
    }
}
