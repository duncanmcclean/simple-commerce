<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\TaxEngine as Contract;

class TaxEngine extends Contract
{
    public function name(): string
    {
        return 'Default';
    }

    public function calculate(Order $order, array $lineItem): TaxCalculation
    {
        return new TaxCalculation;
    }
}
