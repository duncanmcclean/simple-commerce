<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers;

use DuncanMcClean\SimpleCommerce\Contracts\CartDriver;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Exceptions\OrderNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderAPI;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Sites\Site as ASite;

class CookieDriver implements CartDriver
{
    public function getCartKey(): string
    {
        if (Blink::has($this->getKey())) {
            return Blink::get($this->getKey());
        }

        return Cookie::get($this->getKey());
    }

    public function getCart(): Order
    {
        if (! $this->hasCart()) {
            return $this->makeCart();
        }

        try {
            return OrderAPI::findOrFail($this->getCartKey());
        } catch (OrderNotFound $e) {
            return $this->makeCart();
        }
    }

    public function hasCart(): bool
    {
        if (Blink::has($this->getKey())) {
            return true;
        }

        return Cookie::has($this->getKey());
    }

    public function makeCart(): Order
    {
        $cart = OrderAPI::make();
        $cart->set('site', $this->guessSiteFromRequest()->handle());
        $cart->save();

        Cookie::queue(
            $this->getKey(),
            $cart->id,
            Config::get('simple-commerce.cart.expiration', 0)
        );

        // Because the cookie won't be set until the end of the request,
        // we need to set it somewhere for the remainder of the request.
        // And that somewhere is Blink.
        Blink::put($this->getKey(), $cart->id);

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

        if ($referer = request()->header('referer')) {
            foreach (Site::all()->reverse() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        foreach (Site::all()->reverse() as $site) {
            if (Str::contains(request()->url(), $site->url())) {
                return $site;
            }
        }

        return Site::current();
    }

    protected function getKey(): string
    {
        $key = Config::get('simple-commerce.cart.key', 'simple-commerce-cart');
        $site = $this->guessSiteFromRequest();

        if (Site::hasMultiple() && ! Config::get('simple-commerce.cart.single_cart')) {
            return $key.'-'.$site->handle();
        }

        return $key;
    }
}
