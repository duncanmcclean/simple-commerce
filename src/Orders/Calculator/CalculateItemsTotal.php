<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;

class CalculateItemsTotal
{
    public function handle(Order $order, Closure $next)
    {
        $order->itemsTotal(
            $order->lineItems()->map(fn (LineItem $lineItem) => $lineItem->total())->sum()
        );

        return $next($order);
    }
}
