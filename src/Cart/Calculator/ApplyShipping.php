<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;

class ApplyShipping
{
    public function handle(Cart $cart, Closure $next)
    {
        $shippingMethod = $cart->get('shipping_method') ?? config('statamic.simple-commerce.shipping.default_method');

        if (! $shippingMethod) {
            return $next($cart);
        }

        $cart->shippingTotal(ShippingMethod::find($shippingMethod)->cost($cart));

        return $next($cart);
    }
}
