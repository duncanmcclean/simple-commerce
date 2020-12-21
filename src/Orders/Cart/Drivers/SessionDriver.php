<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as ASite;

class SessionDriver implements CartDriver
{
    public function getSessionCartKey(): string
    {
        return Session::get(Config::get('simple-commerce.cart.key'));
    }

    public function getSessionCart(): CartRepository
    {
        return Cart::find($this->getSessionCartKey());
    }

    public function hasSessionCart(): bool
    {
        return Session::has(Config::get('simple-commerce.cart.key'));
    }

    public function makeSessionCart(): CartRepository
    {
        $cart = Cart::make()
            ->site($this->guessSiteFromRequest())
            ->save();

        Session::put(config('simple-commerce.cart.key'), $cart->id);

        return $cart;
    }

    public function getOrMakeSessionCart(): CartRepository
    {
        if ($this->hasSessionCart()) {
            return $this->getSessionCart();
        }

        return $this->makeSessionCart();
    }

    public function forgetSessionCart()
    {
        Session::forget(config('simple-commerce.cart.key'));
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
