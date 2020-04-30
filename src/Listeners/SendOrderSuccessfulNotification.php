<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful as OrderSuccessfulEvent;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice\OrderSuccessful as BackOfficeOrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Notifications\OrderSuccessful;
use Illuminate\Support\Facades\Notification;

class SendOrderSuccessfulNotification
{
    public function handle(OrderSuccessfulEvent $event)
    {
        $event->order->customer->notify(new OrderSuccessful($event->order));

        Notification::route('mail', config('simple-commerce.notifications.mail_to'))
            ->route('slack', config('simple-commerce.notifications.slack_webhook'))
            ->notify(new BackOfficeOrderSuccessful($event->order, $event->order->customer()));
    }
}
