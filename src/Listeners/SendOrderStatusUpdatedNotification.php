<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Order;

class SendOrderStatusUpdatedNotification
{
    public function handle(OrderStatusUpdated $event)
    {
        $this->sendCustomerNotification($event->order);
    }

    protected function sendCustomerNotification(Order $order)
    {
        $order
            ->customer
            ->notify(new OrderStatusUpdated($order, $order->customer));
    }
}
