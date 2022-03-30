<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Site;

class ShippingTags extends SubTag
{
    use CartDriver;

    public function methods()
    {
        $order = $this->getCart();

        return SimpleCommerce::shippingMethods(Site::current()->handle())
            ->map(function ($method) use ($order) {
                $instance = Shipping::use($method);

                if (! $shipingAddress = $order->shippingAddress()) {
                    return null;
                }

                if ($instance->checkAvailability($order, $shipingAddress) === false) {
                    return null;
                }

                $cost = $instance->calculateCost($order);

                return [
                    'handle'      => $method,
                    'name'        => $instance->name(),
                    'description' => $instance->description(),
                    'cost'        => Currency::parse($cost, Site::current()),
                ];
            })
            ->whereNotNull()
            ->toArray();
    }
}
