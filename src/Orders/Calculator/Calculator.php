<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use Illuminate\Support\Facades\Pipeline;

class Calculator
{
    public static function calculate(Order $order): OrderCalculation
    {
        if ($order->paymentStatus()->is(PaymentStatus::Paid)) {
            return new OrderCalculation($order);
        }

        return Pipeline::send(new OrderCalculation($order))
            ->through([
                LineItemCalculator::class,
                LineItemTaxCalculator::class,
                CalculateItemsTotal::class,
                CouponCalculator::class,
                ShippingCalculator::class,
                GrandTotalCalculator::class,
            ])
            ->thenReturn();
    }
}
