<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\VariantLowStock;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice\VariantStockRunningLow as VariantStockRunningLowNotification;
use Illuminate\Support\Facades\Notification;

class SendVariantStockRunningLowNotification
{
    /**
     * @param VariantLowStock $event
     */
    public function handle(VariantLowStock $event)
    {
        $this->SendVariantStockRunningLowNotification($event->variant);
    }

    /**
     * @param Variant $variant
     */
    protected function sendVariantStockRunningLowNotification(Variant $variant)
    {
        Notification::route('mail', config('simple-commerce.notifications.mail.to'))
            ->route('slack', config('simple-commerce.notifications.slack.webhook_url'))
            ->notify(new VariantStockRunningLowNotification($variant));
    }
}
