<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class LineItemTaxCalculator
{
    public function handle(OrderCalculation $orderCalculation, Closure $next)
    {
        $orderCalculation->order->lineItems()
            ->transform(function (LineItem $lineItem) use ($orderCalculation) {
                $taxEngine = SimpleCommerce::taxEngine();
                $taxCalculation = $taxEngine->calculate($orderCalculation->order, $lineItem->toArray()); // TODO: Make this accept a LineItem object.

                $lineItem->tax($taxCalculation->toArray());

                if ($taxCalculation->priceIncludesTax()) {
                    $lineItem->total($taxCalculation->amount());

                    $orderCalculation->order->taxTotal(
                        $orderCalculation->order->taxTotal() + $taxCalculation->amount()
                    );
                } else {
                    $orderCalculation->order->taxTotal(
                        $orderCalculation->order->taxTotal() + $taxCalculation->amount()
                    );
                }

                $orderCalculation->order->itemsTotal(
                    $orderCalculation->order->itemsTotal() + $lineItem->total()
                );

                return $lineItem;
            });

        return $next($orderCalculation);
    }
}
