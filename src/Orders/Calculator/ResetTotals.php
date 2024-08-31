<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;

class ResetTotals
{
    public function handle(Cart $cart, Closure $next)
    {
        $cart->grandTotal(0);
        $cart->subTotal(0);
        $cart->taxTotal(0);
        $cart->shippingTotal(0);
        $cart->couponTotal(0);

        $cart->lineItems()->transform(function (LineItem $lineItem) {
            $lineItem->total(0);

            return $lineItem;
        });

        return $next($cart);
    }
}
