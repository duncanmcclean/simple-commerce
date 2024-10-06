<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Facades\Shipping;
use Statamic\Facades\Site;

class ApplyShipping
{
    public function handle(Order $order, Closure $next)
    {
        $shippingMethod = $order->get('shipping_method');
        $defaultShippingMethod = config('statamic.simple-commerce.sites.'.Site::current()->handle().'.shipping.default_method');

        if (! $shippingMethod && ! $defaultShippingMethod) {
            return $next($order);
        }

        $order->shippingTotal(
            Shipping::site(Site::current()->handle())
                ->use($shippingMethod ?? $defaultShippingMethod)
                ->calculateCost($order)
        );

        return $next($order);
    }
}
