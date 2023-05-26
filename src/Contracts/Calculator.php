<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Calculator
{
    public static function calculate(Order $order): Order;
}
