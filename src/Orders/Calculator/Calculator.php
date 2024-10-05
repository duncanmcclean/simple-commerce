<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use Illuminate\Support\Facades\Pipeline;

class Calculator
{
    public static function calculate(Cart $cart): Cart
    {
        return Pipeline::send($cart)
            ->through([
                ResetTotals::class,
                CalculateLineItems::class,
                ApplyCouponDiscounts::class,
//                ApplyShipping::class,
                CalculateGrandTotal::class,
            ])
            ->thenReturn();
    }
}
