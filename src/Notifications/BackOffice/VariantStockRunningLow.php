<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice;

use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class VariantStockRunningLow extends Notification
{
    public $variant;

    public function __construct(Variant $variant)
    {
        $this->variant = $variant;
    }

    public function via($notifiable): array
    {
        return config('simple-commerce.notifications.notifications.'.static::class);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->from(config('simple-commerce.notifications.mail.from.address'), config('simple-commerce.notifications.mail.from.name'))
            ->subject("Low Stock - {$this->variant->name}")
            ->line("The variant {$this->variant->name} ({$this->variant->sku}) is running low on stock. The current stock level is {$this->variant->stock}.");
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->error()
            ->from('Simple Commerce', ':shopping_trolley:')
            ->content("{$this->variant->name} ({$this->variant->sku}) is running low on stock. The current stock level is {$this->variant->stock}.");
    }
}
