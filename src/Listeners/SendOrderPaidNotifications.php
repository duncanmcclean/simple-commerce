<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Customers\Customer;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Notifications\CustomerOrderPaid;
use Illuminate\Support\Facades\Notification;

class SendOrderPaidNotifications
{
    public function handle(OrderPaid $event)
    {
        if ($customer = $event->order->customer()) {
            Notification::send([$event->order->customer()], new CustomerOrderPaid($event->order));
        } elseif ($event->order->has('email')) {
            $fakeCustomer = (new Customer)
                ->set('email', $this->order->get('email'));

            Notification::send([$fakeCustomer], new CustomerOrderPaid($event->order));
        }

        // TODO: send back-office notifications
        // if (config('simple-commerce.notifications.back_office.order_paid')) {
        //     Mail::send(new OrderPaid($this->id));
        // }
    }
}
