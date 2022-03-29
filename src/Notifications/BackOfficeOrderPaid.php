<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Statamic\Facades\Site;

class BackOfficeOrderPaid extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Order: {$this->order->get('title')}")
            ->line("Order **{$this->order->get('title')}** has just been paid and is ready for fulfilment.")
            ->line('# Order Details')
            ->line('Grand Total: ' . Currency::parse($this->order->grandTotal(), Site::current()))
            ->line('Items Total: ' . Currency::parse($this->order->get('items_total'), Site::current()))
            ->line('Shipping Total: ' . Currency::parse($this->order->get('shipping_total'), Site::current()))
            ->line('Customer: ' . optional($this->order->customer())->email() ?? 'Guest')
            ->line('Payment Gateway: ' . optional($this->order->gateway())['display'] ?? 'N/A');
    }
}
