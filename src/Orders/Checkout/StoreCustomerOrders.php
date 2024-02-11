<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Checkout;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;

class StoreCustomerOrders
{
    public function handle(Order $order, Closure $next)
    {
        if (! isset(SimpleCommerce::customerDriver()['model']) && $order->customer()) {
            $order->customer()->merge([
                'orders' => $order->customer()->orders()
                    ->pluck('id')
                    ->push($order->id())
                    ->toArray(),
            ]);

            $order->customer()->save();
        }

        return $next($order);
    }
}
