<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\TaxEngine as Contract;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxRate as StandardTaxRate;
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

        return new TaxCalculation($itemTax, $taxRate->rate(), $taxRate->includeInPrice());
    }

    protected function decideOnRate(Order $order, array $lineItem): ?StandardTaxRate
    {
        $product = Product::find($lineItem['product']);

        /** @var \DoubleThreeDigital\SimpleCommerce\Orders\Address */
        $address = config('simple-commerce.tax_engine_config.address') === 'billing'
            ? $order->billingAddress()
            : $order->shippingAddress();

        if (! $address) {
            // Do something if we don't have a proper address, maybe use a default address?
        }

        $taxRateQuery = TaxRate::all()
            ->filter(function ($taxRate) use ($product) {
                return $taxRate->category()->id() === $product->taxCategory()->id();
            });

        $taxZoneQuery = TaxZone::all();

        if ($address->country()) {
            $taxZoneQuery = $taxZoneQuery->filter(function ($taxZone) use ($address) {
                return $taxZone->country() === $address->country();
            });

            if ($address->region()) {
                $taxZoneQuery = $taxZoneQuery->filter(function ($taxZone) use ($address) {
                    return $taxZone->region() === $address->region();
                });
            }
        }

        return $taxRateQuery
            ->filter(function ($taxRate) use ($taxZoneQuery) {
                return $taxRate->zone()->id() === $taxZoneQuery->first()->id();
            })
            ->first();
    }
}
