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

            $taxBreakdown = app(TaxDriver::class)
                ->setAddress($cart->taxableAddress())
                ->setPurchasable($lineItem->variant() ?? $lineItem->product())
                ->setLineItem($lineItem)
                ->getBreakdown($lineItemTotal);

            $taxBreakdowns = $taxBreakdowns->merge($taxBreakdown);

            $lineItem->set('tax_breakdown', $taxBreakdown->toArray());
            $lineItem->taxTotal($taxBreakdown->sum('amount'));

            if (config('statamic.simple-commerce.taxes.price_includes_tax')) {
                $lineItem->total($lineItemTotal);
            } else {
                $lineItem->total($lineItemTotal + $lineItem->taxTotal());
            }
        });

        $shippingOption = $cart->shippingOption();

        if ($shippingOption) {
            $shippingTotal = $cart->shippingTotal();

            $shippingTaxBreakdown = app(TaxDriver::class)
                ->setAddress($cart->taxableAddress())
                ->setPurchasable($shippingOption)
                ->getBreakdown($shippingTotal);

            $taxBreakdowns = $taxBreakdowns->merge($shippingTaxBreakdown);

            $cart->set('shipping_tax_breakdown', $shippingTaxBreakdown->toArray());
            $cart->set('shipping_tax_total', $shippingTaxTotal = $shippingTaxBreakdown->sum('amount'));

            if (config('statamic.simple-commerce.taxes.price_includes_tax')) {
                $cart->shippingTotal($shippingTotal);
            } else {
                $cart->shippingTotal($shippingTotal + $shippingTaxTotal);
            }
        }

        $cart->taxTotal($taxBreakdowns->sum('amount'));

        return $next($cart);
    }
}
