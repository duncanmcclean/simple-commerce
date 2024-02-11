<?php

namespace DuncanMcClean\SimpleCommerce\Tax;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Contracts\TaxEngine;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
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

    public function calculateForLineItem(Order $order, LineItem $lineItem): TaxCalculation
    {
        $product = $lineItem->product();

        if ($product->get('exempt_from_tax') === true) {
            return new TaxCalculation;
        }

        if ($this->includedInPrices) {
            $taxAmount = $lineItem->total() / (100 + $this->taxRate) * $this->taxRate;
        } else {
            $taxAmount = $lineItem->total() * ($this->taxRate / 100);
        }

        return new TaxCalculation(
            (int) round($taxAmount),
            $this->taxRate,
            $this->includedInPrices
        );
    }

    public function calculateForShipping(Order $order, ShippingMethod $shippingMethod): TaxCalculation
    {
        if (! config('simple-commerce.tax_engine_config.shipping_taxes')) {
            return new TaxCalculation;
        }

        $taxAmount = $order->shippingTotal() / 100 * $this->taxRate;

        return new TaxCalculation(
            (int) round($taxAmount),
            $this->taxRate,
            $this->includedInPrices
        );
    }
}
