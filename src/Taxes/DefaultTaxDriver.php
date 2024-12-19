<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\Driver as DriverContract;
use Illuminate\Support\Collection;

class DefaultTaxDriver implements DriverContract
{
    // todo: add this method to the interface once we have the signature down
    public function getBreakdown(Cart $cart, Purchasable $purchasable, int $total): Collection
    {
        $breakdown = collect();
        $taxRates = (new GetTaxRates)($cart, $purchasable->purchasableTaxClass());

        if (config('statamic.simple-commerce.taxes.price_includes_tax')) {
            $totalTaxPercentage = $taxRates->sum() / 100; // E.g. 0.2 for 20%
            $priceExcludingTax = round($total / (1 + $totalTaxPercentage));

            foreach ($taxRates as $taxRate) {
                $taxAmount = round($priceExcludingTax * ($taxRate / 100));

                $breakdown->push([
                    'rate' => $taxRate,
                    'description' => 'TODO',
                    'zone' => 'TODO',
                    'amount' => $taxAmount,
                ]);
            }

            return $breakdown;
        }

        foreach ($taxRates as $taxRate) {
            $taxAmount = round($total * ($taxRate / 100));

            $breakdown->push([
                'rate' => $taxRate,
                'description' => 'TODO',
                'zone' => 'TODO',
                'amount' => $taxAmount,
            ]);
        }

        return $breakdown;
    }
}