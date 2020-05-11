<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderSuccessful extends Notification
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
            ->from(config('simple-commerce.notifications.mail.from.address'), config('simple-commerce.notifications.mail.from.name'))
            ->subject("Order #{$this->order->id}")
            ->markdown('simple-commerce::mail.order-successful', [
                'order' => $this->order,
                'customer' => $this->order->customer,
            ])
            ->attach($this->order->generateReceipt(true), [
                'as' => 'receipt.pdf',
                'mime' => 'text/pdf',
            ]);
    }
}
