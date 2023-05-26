<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\Tax\TaxCalculation;

interface TaxEngine
{
    public function name(): string;

    public function calculate(Order $order, LineItem $lineItem): TaxCalculation;
}
