<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class LineItemTaxCalculator
{
    public function handle(Order $order, Closure $next)
    {
        $order->lineItems()
            ->transform(function (LineItem $lineItem) use ($order) {
                $taxEngine = SimpleCommerce::taxEngine();
                $taxCalculation = $taxEngine->calculate($order, $lineItem);

                $lineItem->tax($taxCalculation->toArray());

                if ($taxCalculation->priceIncludesTax()) {
                    $lineItem->total($taxCalculation->amount());

                    $order->taxTotal(
                        $order->taxTotal() + $taxCalculation->amount()
                    );
                } else {
                    $order->taxTotal(
                        $order->taxTotal() + $taxCalculation->amount()
                    );
                }

                return $lineItem;
            });

        return $next($order);
    }
}
