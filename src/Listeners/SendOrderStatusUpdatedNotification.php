<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Order;

class SendOrderStatusUpdatedNotification
{
    /**
     * @param OrderStatusUpdated $event
     */
    public function handle(OrderStatusUpdated $event)
    {
        $this->sendCustomerNotification($event->order);
    }

    /**
     * @param Order $order
     */
    protected function sendCustomerNotification(Order $order)
    {
        $order
            ->customer
            ->notify(new OrderStatusUpdated($order, $order->customer));
    }
}
