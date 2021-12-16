<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Exceptions\EntryNotFound;
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
        return Cookie::get($this->getKey());
    }

    public function getCart(): Order
    {
        if (! $this->hasCart()) {
            return $this->makeCart();
        }

        try {
            return OrderAPI::find($this->getCartKey());
        } catch (EntryNotFound $e) {
            return $this->makeCart();
        }
    }

    public function hasCart(): bool
    {
        return Cookie::has($this->getKey());
    }

    public function makeCart(): Order
    {
        $cart = OrderAPI::create(
            [],
            $this->guessSiteFromRequest()
        );

        Cookie::queue($this->getKey(), $cart->id);

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
            Cookie::forget($this->getKey())
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

    protected function getKey(): string
    {
        $site = $this->guessSiteFromRequest();

        if (Site::hasMultiple() && ! Config::get('simple-commerce.cart.single_cart')) {
            return Config::get('simple-commerce.cart.key').'-'.$site->handle();
        }

        return Config::get('simple-commerce.cart.key');
    }
}
