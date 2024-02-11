<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Tax\TaxCalculation;

interface TaxEngine
{
    public function name(): string;

    public function calculateForLineItem(Order $order, LineItem $lineItem): TaxCalculation;

    public function calculateForShipping(Order $order, ShippingMethod $shippingMethod): TaxCalculation;
}
