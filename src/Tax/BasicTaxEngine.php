<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\TaxEngine;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Site;

class BasicTaxEngine implements TaxEngine
{
    protected $taxRate;
    protected $includedInPrices;

    public function __construct()
    {
        $taxConfiguration = collect(Config::get('simple-commerce.sites'))
            ->get(Site::current()->handle())['tax'];

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

        $taxAmount = ($lineItem['total'] / 100) * ($this->taxRate / (100 + $this->taxRate));
        $itemTax = (int) round($taxAmount * 100);

        return new TaxCalculation($itemTax, $this->taxRate, $this->includedInPrices);
    }
}
