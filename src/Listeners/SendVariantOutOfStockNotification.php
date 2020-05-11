<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice\VariantOutOfStock as VariantOutOfStockNotification;
use Illuminate\Support\Facades\Notification;

class SendVariantOutOfStockNotification
{
    public function handle(VariantOutOfStock $event)
    {
        $this->sendBackOfficeNotification($event->variant);
    }

    protected function sendBackOfficeNotification(Variant $variant)
    {
        Notification::route('mail', config('simple-commerce.notifications.mail.to'))
            ->route('slack', config('simple-commerce.notifications.slack.webhook_url'))
            ->notify(new VariantOutOfStockNotification($variant));
    }
}
