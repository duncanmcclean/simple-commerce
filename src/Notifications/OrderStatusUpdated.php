<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    public $order;
    public $customer;

    public function __construct($order, $customer)
    {
        $this->order = $order;
        $this->customer = $customer;
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
            ->subject('Order status updated')
            ->markdown('simple-commerce::mail.order-updated', [
                'order' => $this->order,
                'customer' => $this->customer,
            ]);
    }
}
