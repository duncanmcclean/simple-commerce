<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice;

use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class OrderSuccessful extends Notification
{
    public $order;
    public $customer;
    public $total;

    public function __construct($order, $customer)
    {
        $this->order = $order;
        $this->customer = $customer;

        $this->total = (new Currency)->parse($this->order->total);
    }

    public function via($notifiable)
    {
        return config('commerce.notifications.channel');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->success()
            ->subject('Successful Order')
            ->line("Order #{$this->order->id} has been successfully completed. The total was {$this->total}.");
    }

    public function toSlack($notifiable)
    {
        $order = $this->order;
        $customer = $this->customer;
        $total = $this->total;

        return (new SlackMessage)
            ->success()
            ->content('Successful Order')
            ->attachment(function ($attachment) use ($order, $customer, $total) {
                $attachment->title("Order #{$order->id}", config('app.url'))
                    ->fields([
                        'Customer' => $customer->name,
                        'Total' => $total,
                    ]);
            });
    }
}
