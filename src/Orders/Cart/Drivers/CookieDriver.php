<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderAPI;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as ASite;

class CookieDriver implements CartDriver
{
    public function getCartKey(): string
    {
        return Cookie::get(Config::get('simple-commerce.cart.key'));
    }

    public function getCart(): Order
    {
        if (! $this->hasCart()) {
            return $this->makeCart();
        }

        return OrderAPI::find($this->getCartKey());
    }

    public function hasCart(): bool
    {
        return Cookie::has(Config::get('simple-commerce.cart.key'));
    }

    public function makeCart(): Order
    {
        $cart = OrderAPI::create(
            [],
            $this->guessSiteFromRequest()
        );

        Cookie::queue(config('simple-commerce.cart.key'), $cart->id);

        return $cart;
    }

    public function getOrMakeCart(): Order
    {
        if ($this->hasCart()) {
            return $this->getCart();
        }

        return $this->makeCart();
    }

    public function forgetCart()
    {
        Cookie::queue(
            Cookie::forget(Config::get('simple-commerce.cart.key'))
        );
    }

    protected function guessSiteFromRequest(): ASite
    {
        if ($site = request()->get('site')) {
            return Site::get($site);
        }

        foreach (Site::all() as $site) {
            if (Str::contains(request()->url(), $site->url())) {
                return $site;
            }
        }

        if ($referer = request()->header('referer')) {
            foreach (Site::all() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        return Site::current();
    }
}
