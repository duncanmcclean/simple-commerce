<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingOption;

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

        return $next($cart);
    }
}
