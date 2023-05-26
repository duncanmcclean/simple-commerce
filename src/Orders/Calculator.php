<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator\OrderCalculation;
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
                Calculator\LineItemCalculator::class,
                Calculator\LineItemTaxCalculator::class,
                Calculator\CouponCalculator::class,
                Calculator\ShippingCalculator::class,
                Calculator\GrandTotalCalculator::class,
            ])
            ->thenReturn();
    }
}
