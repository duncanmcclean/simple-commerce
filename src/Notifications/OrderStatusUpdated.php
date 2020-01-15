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

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->success()
            ->subject('Order status updated')
            ->markdown('commerce::mail.order-updated', [
                'order' => $this->order,
                'customer' => $this->customer,
            ]);
    }
}
