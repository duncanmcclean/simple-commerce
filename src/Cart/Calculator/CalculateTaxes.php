<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\Driver as TaxDriver;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Products\ProductType;

class CalculateTaxes
{
    public function handle(Cart $cart, Closure $next)
    {
        $cart->lineItems()->each(function (LineItem $lineItem) use ($cart) {
            $lineItemTotal = $lineItem->total();

            $taxBreakdown = app(TaxDriver::class)->getBreakdown($cart, $lineItem);

            // todo: update the line item's tax total
            // todo: update the cart's tax total
        });

        // TODO: shipping taxes

        return $next($cart);
    }
}
