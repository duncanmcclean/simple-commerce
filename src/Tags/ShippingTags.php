<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Currency;
use DuncanMcClean\SimpleCommerce\Facades\Shipping;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Site;

class ShippingTags extends SubTag
{
    use CartDriver;

    public function methods()
    {
        $order = $this->getCart();

        return SimpleCommerce::shippingMethods(Site::current()->handle())
            ->map(function ($shippingMethod) use ($order) {
                $instance = Shipping::site(Site::current()->handle())
                    ->use($shippingMethod['handle']);

                if (! $shipingAddress = $order->shippingAddress()) {
                    return null;
                }

                if ($instance->checkAvailability($order, $shipingAddress) === false) {
                    return null;
                }

                $cost = $instance->calculateCost($order);

                return [
                    'handle' => $shippingMethod['handle'],
                    'name' => $instance->name(),
                    'description' => $instance->description(),
                    'cost' => Currency::parse($cost, Site::current()),
                ];
            })
            ->whereNotNull()
            ->values()
            ->toArray();
    }
}
