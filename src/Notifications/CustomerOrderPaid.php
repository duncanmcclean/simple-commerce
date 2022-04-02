<?php

namespace DoubleThreeDigital\SimpleCommerce\Notifications;

use Barryvdh\DomPDF\Facade as PDF;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOrderPaid extends Notification
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
        $pdf = PDF::loadView('simple-commerce::receipt', array_merge(
            $this->order->toAugmentedArray(),
            [
                'config' => [
                    'app' => config('app'),
                ],
            ],
        ));

        return (new MailMessage)
            ->subject('Thanks for your order!')
            ->line('Thanks for your order! Your order receipt has been attached to this email.')
            ->line('Please get in touch if you have any questions.')
            ->attachData(
                $pdf->output(),
                'receipt.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
