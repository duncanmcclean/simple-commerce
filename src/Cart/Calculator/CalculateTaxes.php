<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\Driver as TaxDriver;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;

class CalculateTaxes
{
    public function handle(Cart $cart, Closure $next)
    {
        $taxBreakdowns = collect();

        $cart->lineItems()->each(function (LineItem $lineItem) use ($cart, &$taxBreakdowns) {
            $lineItemTotal = $lineItem->total();

            if ($lineItem->get('discount_amount')) {
                $lineItemTotal -= $lineItem->get('discount_amount');
            }

            $taxBreakdown = app(TaxDriver::class)->getBreakdown(
                $cart,
                $lineItem->variant() ?? $lineItem->product(),
                $lineItemTotal
            );

            $taxBreakdowns = $taxBreakdowns->merge($taxBreakdown);

            $lineItem->set('tax_breakdown', $taxBreakdown->all());
            $lineItem->taxTotal($taxBreakdown->sum('amount'));

            if (config('statamic.simple-commerce.taxes.price_includes_tax')) {
                $lineItem->total($lineItemTotal);
            } else {
                $lineItem->total($lineItemTotal + $lineItem->taxTotal());
            }
        });

        // todo: shipping

        $cart->taxTotal($taxBreakdowns->sum('amount'));

        return $next($cart);
    }
}
