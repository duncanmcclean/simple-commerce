<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

interface Calculator
{
    public static function calculate(Order $order): Order;
}
