<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use Statamic\Facades\Site;

class ShippingCalculator
{
    public function handle(OrderCalculation $orderCalculation, Closure $next)
    {
        $shippingMethod = $orderCalculation->order->get('shipping_method');
        $defaultShippingMethod = config('simple-commerce.sites.'.Site::current()->handle().'.shipping.default_method');

        if (! $shippingMethod && ! $defaultShippingMethod) {
            return $next($orderCalculation);
        }

        $orderCalculation->order->shippingTotal(
            Shipping::site(Site::current()->handle())
                ->use($shippingMethod ?? $defaultShippingMethod)
                ->calculateCost($orderCalculation->order)
        );

        return $next($orderCalculation);
    }
}
