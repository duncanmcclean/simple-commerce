<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;

class ResetTotals
{
    public function handle(Cart $cart, Closure $next)
    {
        $cart->grandTotal(0);
        $cart->subTotal(0);
        $cart->taxTotal(0);
        $cart->shippingTotal(0);
        $cart->discountTotal(0);

        $cart->remove('shipping_tax_total');
        $cart->remove('shipping_tax_breakdown');

        $cart->lineItems()->transform(function (LineItem $lineItem) {
            $lineItem->total(0);
            $lineItem->unitPrice(0);
            $lineItem->taxTotal(0);

            $lineItem->remove('tax_breakdown');

            return $lineItem;
        });

        return $next($cart);
    }
}
