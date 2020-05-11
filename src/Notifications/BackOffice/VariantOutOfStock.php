<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice;

use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class VariantOutOfStock extends Notification
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
            ->subject("Out of Stock - {$this->variant->name}")
            ->line("The variant {$this->variant->name} ({$this->variant->sku}) is now out of stock.");
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->error()
            ->from('Simple Commerce', ':shopping_trolley:')
            ->content("{$this->variant->name} ({$this->variant->sku}) is out of stock.");
    }
}
