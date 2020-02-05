<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice\OrderSuccessful as BackOfficeOrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Notifications\OrderSuccessful;
use Illuminate\Support\Facades\Notification;

class SendOrderSuccessfulNotification
{
    public function handle(CheckoutComplete $event)
    {
        $event->customer->notify(new OrderSuccessful($event->order, $event->customer));

        Notification::route('mail', config('commerce.notifications.mail_to'))
            ->route('slack', config('commerce.notifications.slack_webhook'))
            ->notify(new BackOfficeOrderSuccessful($event->order, $event->customer));
    }
}
