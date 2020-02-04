<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications;

use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
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

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->success()
            ->subject("Your order #{$this->order->id} has been refunded")
            ->line('Your order from '.config('app.name').' has been refunded. The order total was '.(new Currency())->parse($this->order->total).'.');
    }
}
