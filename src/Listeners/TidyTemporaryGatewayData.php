<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Events\PostCheckout;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;

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
