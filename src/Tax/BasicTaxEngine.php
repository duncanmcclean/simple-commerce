<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\TaxEngine;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use Illuminate\Support\Facades\Config;

class BasicTaxEngine implements TaxEngine
{
    protected $taxRate;
    protected $includedInPrices;

    public function __construct()
    {
        $taxConfiguration = Config::get('simple-commerce.tax_engine_config');

        $this->taxRate = $taxConfiguration['rate'];
        $this->includedInPrices = $taxConfiguration['included_in_prices'];
    }

    public function name(): string
    {
        return __('Basic Tax Engine');
    }

    public function calculate(Order $order, array $lineItem): TaxCalculation
    {
        $product = Product::find($lineItem['product']);

        if ($product->get('exempt_from_tax') === true) {
            return new TaxCalculation;
        }

        if ($this->includedInPrices) {
            $taxAmount = $lineItem['total'] / (100 + $this->taxRate) * $this->taxRate;
        } else {
            $taxAmount = $lineItem['total'] * ($this->taxRate / 100);
        }

        return new TaxCalculation(
            (int) round($taxAmount),
            $this->taxRate,
            $this->includedInPrices
        );
    }
}
