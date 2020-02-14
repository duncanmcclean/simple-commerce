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

    public function via($notifiable)
    {
        return config('simple-commerce.notifications.channel');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->error()
            ->subject('Variant out of stock')
            ->line("{$this->variant->name} ({$this->variant->sku}) is out of stock.");
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->error()
            ->content("{$this->variant->name} ({$this->variant->sku}) is out of stock.");
    }
}
