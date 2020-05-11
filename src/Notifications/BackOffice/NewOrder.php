<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class NewOrder extends Notification
{
    public $order;
    public $customer;
    public $total;

    public function __construct($order, $customer)
    {
        $this->order = $order;
        $this->customer = $customer;

        $this->total = Currency::parse($this->order->total);
    }

    public function via($notifiable): array
    {
        return config('simple-commerce.notifications.notifications.'.static::class);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->from(config('simple-commerce.notifications.mail.from.address'), config('simple-commerce.notifications.mail.from.name'))
            ->subject('Successful Order')
            ->line("Order #{$this->order->id} has been successfully completed. The total was {$this->total}.");
    }

    public function toSlack($notifiable): SlackMessage
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
