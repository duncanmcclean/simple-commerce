<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
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
                $instance = new $method();

                if ($instance->checkAvailability($cart->shippingAddress()->toArray()) === false) {
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
