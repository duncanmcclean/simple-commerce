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
        $shippingMethod = $cart->get('shipping_method');
        $shippingOption = $cart->get('shipping_option');

        if (! $shippingMethod || ! $shippingOption) {
            return $next($cart);
        }

        $cart->shippingTotal(
            ShippingMethod::find($shippingMethod)->options($cart)->firstWhere('handle', $shippingOption)->price()
        );

        return $next($cart);
    }
}
