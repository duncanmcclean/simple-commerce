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
            ->error()
            ->from(config('simple-commerce.notifications.mail.from.address'), config('simple-commerce.notifications.mail.from.name'))
            ->subject('Variant out of stock')
            ->line("{$this->variant->name} ({$this->variant->sku}) is out of stock.");
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->error()
            ->content("{$this->variant->name} ({$this->variant->sku}) is out of stock.");
    }
}
