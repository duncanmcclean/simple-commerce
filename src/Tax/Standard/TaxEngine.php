<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\TaxEngine as Contract;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tax\TaxCalculation;

class TaxEngine implements Contract
{
    public function name(): string
    {
        return 'Standard';
    }

    public function calculate(Order $order, array $lineItem): TaxCalculation
    {
        $taxRate = $this->decideOnRate($order, $lineItem);

        $taxAmount = ($lineItem['total'] / 100) * ($taxRate->rate() / (100 + $taxRate->rate()));
        $itemTax = (int) round($taxAmount * 100);

        return new TaxCalculation($itemTax, $taxRate->rate(), true); // TODO: Included in prices?
    }

    protected function decideOnRate(Order $order, array $lineItem): ?TaxRate
    {
        $product = Product::find($lineItem['product']);

        $taxCategory = $product->taxCategory();


        // Get product
        // Get tax catgeory from product

        // Get country & region information from address

        // Find all tax rates in tax category
        // Find all tax zones that match address information

        // Cross-query the category and zones

        // Return first rate
    }
}
