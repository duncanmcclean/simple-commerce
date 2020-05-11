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
            ->from(config('simple-commerce.notifications.mail.from.address'), config('simple-commerce.notifications.mail.from.name'))
            ->subject("Order #{$this->order->id}")
            ->line("Hi, {$this->customer->name}")
            ->line("The status of your order #{$this->order->id} has been updated. It is now {$this->order->orderStatus->name}.")
            ->line("If you have any questions regarding your order, please contact us.");
    }
}
