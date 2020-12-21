<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as ASite;

class CookieDriver implements CartDriver
{
    public function getCartKey(): string
    {
        return Cookie::get(Config::get('simple-commerce.Cart.key'));
    }

    public function getCart(): CartRepository
    {
        return Cart::find($this->getCartKey());
    }

    public function hasCart(): bool
    {
        return Cookie::has(Config::get('simple-commerce.Cart.key'));
    }

    public function makeCart(): CartRepository
    {
        $cart = Cart::make()
            ->site($this->guessSiteFromRequest())
            ->save();

        Cookie::queue(config('simple-commerce.Cart.key'), $cart->id);

        return $cart;
    }

    public function getOrMakeCart(): CartRepository
    {
        if ($this->hasCart()) {
            return $this->getCart();
        }

        return $this->makeCart();
    }

    public function forgetCart()
    {
        Cookie::forget(config('simple-commerce.Cart.key'));
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
