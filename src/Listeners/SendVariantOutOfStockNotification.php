<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice\VariantOutOfStock as BackOfficeVariantOutOfStock;
use Illuminate\Support\Facades\Notification;

class SendVariantOutOfStockNotification
{
    public function handle(VariantOutOfStock $event)
    {
        Notification::route('mail', config('commerce.notifications.mail_to'))
            ->route('slack', config('commerce.notifications.slack_webhook'))
            ->notify(new BackOfficeVariantOutOfStock($event->variant));
    }
}
