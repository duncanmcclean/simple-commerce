<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Notifications\OrderSuccessful;

class SendOrderSuccessfulNotification
{
    public function handle(CheckoutComplete $event)
    {
        $event->customer->notify(new OrderSuccessful($event->order, $event->customer));
    }
}
