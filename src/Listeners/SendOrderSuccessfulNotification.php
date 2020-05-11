<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful as OrderSuccessfulEvent;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOffice\NewOrder;
use DoubleThreeDigital\SimpleCommerce\Notifications\OrderSuccessful;
use Illuminate\Support\Facades\Notification;

class SendOrderSuccessfulNotification
{
    public function handle(OrderSuccessfulEvent $event)
    {
        $this->sendCustomerNotification($event->order);
        $this->sendBackOfficeNotification($event->order);
    }

    protected function sendCustomerNotification(Order $order)
    {
        $order
            ->customer
            ->notify(new OrderSuccessful($order));
    }

    protected function sendBackOfficeNotification(Order $order)
    {
        Notification::route('mail', config('simple-commerce.notifications.mail.to'))
            ->route('slack', config('simple-commerce.notifications.slack.webhook_url'))
            ->notify(new NewOrder($order, $order->customer));
    }
}
