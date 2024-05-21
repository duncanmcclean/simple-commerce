<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

interface Calculator
{
    public static function calculate(Order $order): Order;
}
