<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Site;

class ShippingTaxCalculator
{
    public function handle(Order $order, Closure $next)
    {
        $defaultShippingMethod = config('simple-commerce.sites.'.Site::current()->handle().'.shipping.default_method');
        $shippingMethod = $order->get('shipping_method') ?? $defaultShippingMethod;

        if (! $shippingMethod) {
            return $next($order);
        }

        $taxEngine = SimpleCommerce::taxEngine();
        $taxCalculation = $taxEngine->calculateForShipping($order, new $shippingMethod);

        $order->set('shipping_tax', $taxCalculation->toArray());

        if ($taxCalculation->priceIncludesTax()) {
            $order->shippingTotal($order->shippingTotal() - $taxCalculation->amount());

            $order->taxTotal(
                $order->taxTotal() + $taxCalculation->amount()
            );
        } else {
            $order->taxTotal(
                $order->taxTotal() + $taxCalculation->amount()
            );
        }

        return $next($order);
    }
}
