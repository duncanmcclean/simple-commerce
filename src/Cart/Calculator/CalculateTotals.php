<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;

class CalculateTotals
{
    public function handle(Cart $cart, Closure $next)
    {
        $pricesIncludeTax = config('statamic.simple-commerce.taxes.price_includes_tax');

        // Calculate the subtotal by summing the line item subtotals (totals without additional taxes)
        $cart->subTotal($cart->lineItems()->map->subTotal()->sum());

        // Calculate the total (subtotal + taxes if they aren't included in the prices)
        $total = $cart->subTotal();

        if (! $pricesIncludeTax) {
            $total += $cart->lineItems()->map->taxTotal()->sum();
        }

        // Apply any discounts to the total before adding shipping.
        $total = $total - $cart->discountTotal();

        // Add shipping costs to the total
        $total += $cart->shippingTotal();

        $cart->grandTotal($total);

        return $next($cart);
    }
}
