<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;

class CalculateItemsTotal
{
    public function handle(Order $order, Closure $next)
    {
        $order->itemsTotal($order->lineItems()->map->total()->sum());

        return $next($order);
    }
}
