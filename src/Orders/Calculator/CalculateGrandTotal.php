<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

class CalculateGrandTotal
{
    public function handle(Cart $cart, Closure $next)
    {
        $cart->grandTotal(
            (($cart->subTotal() + $cart->taxTotal()) - $cart->discountTotal()) + $cart->shippingTotal()
        );

        $cart->grandTotal((int) $cart->grandTotal());

        return $next($cart);
    }
}
