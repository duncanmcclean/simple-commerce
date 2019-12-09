<?php

namespace Damcclean\Commerce\Notifications;

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
        return (new MailMessage())
            ->success()
            ->subject('Order successful')
            ->markdown('commerce::mail.order-successful', [
                'order' => $this->order,
                'customer' => $this->customer,
            ]);
    }
}
