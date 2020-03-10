<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderSuccessful extends Notification
{
    public $order;
    public $customer;

    public function __construct($order, $customer)
    {
        $this->order = $order;
        $this->customer = $customer;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // TODO: fix tables in this view, the formatting is way off

        return (new MailMessage())
            ->success()
            ->subject("Your order #{$this->order->id}")
            ->markdown('simple-commerce::mail.order-successful', [
                'order' => $this->order,
                'customer' => $this->customer,
            ]);
    }
}
