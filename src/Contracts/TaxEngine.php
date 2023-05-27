<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\Tax\TaxCalculation;

interface TaxEngine
{
    public function name(): string;

    public function calculateForLineItem(Order $order, LineItem $lineItem): TaxCalculation;

    public function calculateForShipping(Order $order, ShippingMethod $shippingMethod): TaxCalculation;
}
