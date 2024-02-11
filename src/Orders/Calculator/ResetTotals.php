<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;

class ResetTotals
{
    public function handle(Order $order, Closure $next)
    {
        $order->grandTotal(0);
        $order->itemsTotal(0);
        $order->taxTotal(0);
        $order->shippingTotal(0);
        $order->couponTotal(0);

        $order->lineItems()->transform(function (LineItem $lineItem) {
            $lineItem->total(0);

            return $lineItem;
        });

        return $next($order);
    }
}
