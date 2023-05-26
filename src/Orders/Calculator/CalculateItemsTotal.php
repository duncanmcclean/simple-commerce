<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;

class CalculateItemsTotal
{
    public function handle(OrderCalculation $orderCalculation, Closure $next)
    {
        $orderCalculation->order->itemsTotal(
            $orderCalculation->order->lineItems()->map(fn (LineItem $lineItem) => $lineItem->total())->sum()
        );

        return $next($orderCalculation);
    }
}
