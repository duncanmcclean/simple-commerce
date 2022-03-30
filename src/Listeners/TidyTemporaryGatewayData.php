<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class TidyTemporaryGatewayData
{
    public function handle(PostCheckout $event)
    {
        $order = $event->order;

        collect(SimpleCommerce::gateways())->pluck('handle')->each(function ($gatewayHandle) use (&$order) {
            $order->set($gatewayHandle, null);
        });

        $order->save();
    }
}
