<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRefunded extends Notification
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return config('simple-commerce.notifications.notifications.'.static::class);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->success()
            ->from(config('simple-commerce.notifications.mail.from.address'), config('simple-commerce.notifications.mail.from.name'))
            ->subject("Your order #{$this->order->id} has been refunded")
            ->line('Your order from '.config('app.name').' has been refunded. The order total was '.Currency::parse($this->order->total).'.');
    }
}
