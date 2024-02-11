<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use DuncanMcClean\SimpleCommerce\Contracts\Calculator as Contract;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use Illuminate\Support\Facades\Pipeline;

class Calculator implements Contract
{
    public static function calculate(Order $order): Order
    {
        return Pipeline::send($order)
            ->through([
                ResetTotals::class,
                LineItemCalculator::class,
                LineItemTaxCalculator::class,
                CalculateItemsTotal::class,
                CouponCalculator::class,
                ShippingCalculator::class,
                ShippingTaxCalculator::class,
                CalculateGrandTotal::class,
            ])
            ->thenReturn();
    }
}
