<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Site;

class ShippingTags extends SubTag
{
    public function methods()
    {
        $cart = Cart::find(Session::get(Config::get('simple-commerce.cart_key')));

        $siteConfig = collect(Config::get('simple-commerce.sites'))
            ->get(Site::current()->handle());

        return collect($siteConfig['shipping']['methods'])
            ->map(function ($method) use ($cart) {
                $instance = Shipping::use($method);

                if (! $shipingAddress = $cart->shippingAddress()) {
                    return null;
                }

                if ($instance->checkAvailability($shipingAddress) === false) {
                    return null;
                }

                $cost = $instance->calculateCost($cart->entry());

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
