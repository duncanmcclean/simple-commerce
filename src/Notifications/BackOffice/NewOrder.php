<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class NewOrder extends Notification
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
            ->subject('New Order')
            ->line('A new order has been created. The total of the order is '.Currency::parse($this->order->total).". The customer's receipt has been attached to this email for reference.")
            ->attach($this->order->generateReceipt(true), [
                'as'   => 'receipt.pdf',
                'mime' => 'text/pdf',
            ]);
    }

    public function toSlack($notifiable): SlackMessage
    {
        $order = $this->order;

        return (new SlackMessage())
            ->success()
            ->from('Simple Commerce', ':shopping_trolley:')
            ->content('A new order has been created.')
            ->attachment(function ($attachment) use ($order) {
                $attachment->title("Order #{$order->id}")
                    ->fields([
                        'Customer' => "{$order->customer->name} ({$order->customer->email})",
                        'Total'    => $order->total,
                        'Items'    => implode(', ', $order->lineItems->pluck('description')->toArray()),
                    ]);
            });
    }
}
