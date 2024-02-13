<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers;

use DuncanMcClean\SimpleCommerce\Contracts\CartDriver;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderAPI;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as ASite;

class SessionDriver implements CartDriver
{
    public function getCartKey(): string
    {
        return Session::get($this->getKey());
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
        return Session::has($this->getKey());
    }

    public function makeCart(): Order
    {
        $cart = OrderAPI::make();
        $cart->set('site', $this->guessSiteFromRequest());
        $cart->save();

        Session::put($this->getKey(), $cart->id);

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
        Session::forget($this->getKey());
    }

    protected function guessSiteFromRequest(): ASite
    {
        if ($site = request()->get('site')) {
            return Site::get($site);
        }

        if ($referer = request()->header('referer')) {
            foreach (Site::all() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        foreach (Site::all() as $site) {
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
