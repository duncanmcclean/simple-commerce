<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use Statamic\Facades\Site;

class ShippingCalculator
{
    public function handle(Order $order, Closure $next)
    {
        $shippingMethod = $order->get('shipping_method');
        $defaultShippingMethod = config('simple-commerce.sites.'.Site::current()->handle().'.shipping.default_method');

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
