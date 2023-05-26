<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;

class OrderCalculation
{
    public array $data = [];

    public function __construct(public Order $order)
    {
    }
}
