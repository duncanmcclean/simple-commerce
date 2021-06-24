<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Customers\Customer;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Notifications\CustomerOrderPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOrderPaidNotifications implements ShouldQueue
{
    public function handle(OrderPaid $event)
    {
        foreach (config('simple-commerce.notifications.order_paid') as $notification => $notificationConfig) {
            if ($notificationConfig['to'] === 'customer') {
                if ($customer = $event->order->customer()) {
                    Notification::route('mail', $customer->email())
                        ->notify(new CustomerOrderPaid($event->order));
                } elseif ($event->order->has('email')) {
                    (new Customer)
                        ->set('email', $this->order->get('customer'))
                        ->notify(new $notification($event->order));
                }

                continue;
            }

            if (is_string($notificationConfig['to'])) {
                Notification::route('mail', $notificationConfig['to'])
                    ->notify(new $notification($event->order));

                continue;
            }

            throw new \Exception("You did not specify a recipient for the notification [{$notification}]");
        }
    }
}
