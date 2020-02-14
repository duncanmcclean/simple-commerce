<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\VariantStockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice\VariantStockRunningLow as BackOfficeVariantStockRunningLow;
use Illuminate\Support\Facades\Notification;

class SendVariantStockRunningLowNotification
{
    public function handle(VariantStockRunningLow $event)
    {
        Notification::route('mail', config('simple-commerce.notifications.mail_to'))
            ->route('slack', config('simple-commerce.notifications.slack_webhook'))
            ->notify(new BackOfficeVariantStockRunningLow($event->variant));
    }
}
