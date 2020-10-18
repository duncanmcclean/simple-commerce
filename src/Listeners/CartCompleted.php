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
            if ($event->cart->has('customer')) {
                try {
                    $customer = Customer::find($event->cart->get('customer'));

                    Mail::to($customer->data['email'])
                        ->send(new OrderConfirmation($event->cart->id()));
                } catch (\Exception $e) {
                    // Do nothing
                }
            }
        }

        if (config('simple-commerce.notifications.back_office.order_paid')) {
            Mail::send(new OrderPaid($event->cart->id()));
        }
    }
}
