<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;

class CalculateGrandTotal
{
    public function handle(Cart $cart, Closure $next)
    {
        $cart->grandTotal(
            (($cart->subTotal() + $cart->taxTotal()) - $cart->discountTotal()) + $cart->shippingTotal()
        );

        return $next($cart);
    }
}
