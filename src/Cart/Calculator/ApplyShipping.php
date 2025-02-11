<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;

class ApplyShipping
{
    public function handle(Cart $cart, Closure $next)
    {
        $shippingMethod = $cart->shippingMethod();
        $shippingOption = $cart->shippingOption();

        if (! $shippingMethod || ! $shippingOption) {
            $cart->remove('shipping_method')->remove('shipping_option');

            return $next($cart);
        }

        $cart->shippingTotal($shippingOption->price());
        $cart->set('shipping_option', $shippingOption->toArray());

        return $next($cart);
    }
}
