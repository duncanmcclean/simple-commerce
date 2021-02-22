<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\CartCompleted as Event;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Mail\BackOffice\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

class CartCompleted
{
    public function handle(Event $event)
    {
        if (config('simple-commerce.notifications.customer.order_confirmation')) {
            if ($event->order->has('customer') || $event->order->has('email')) {
                try {
                    $email = $event->order->has('customer')
                        ? $event->cart->customer()
                        : ($event->order->has('email') ? $event->order->get('email') : null);

                    if ($email) {
                        Mail::to($email)->send(new OrderConfirmation($event->order->id));
                    }
                } catch (\Exception $e) {
                    info("Exception when sending Order Confirmation: {$e->getMessage()}");
                }
            }
        }

        if (config('simple-commerce.notifications.back_office.order_paid')) {
            Mail::send(new OrderPaid($event->cart->id));
        }
    }
}
