<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Site;

class ShippingTags extends SubTag
{
    use CartDriver;

    public function methods()
    {
        $order = $this->getCart();

        $siteConfig = collect(Config::get('simple-commerce.sites'))
            ->get(Site::current()->handle());

        return collect($siteConfig['shipping']['methods'])
            ->map(function ($method) use ($order) {
                $instance = Shipping::use($method);

                if (!$shipingAddress = $order->shippingAddress()) {
                    return null;
                }

                if ($instance->checkAvailability($shipingAddress) === false) {
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
