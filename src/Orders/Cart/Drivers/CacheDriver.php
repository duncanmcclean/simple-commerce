<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as ASite;

class CacheDriver implements CartDriver
{
    // We would strongly recommend that you don't use this driver in production environments. It is meant for use in tests suites only.

    public function getCartKey(): string
    {
        return Cache::get(Config::get('simple-commerce.cart.key'));
    }

    public function getCart(): CartRepository
    {
        return Cart::find($this->getCartKey());
    }

    public function hasCart(): bool
    {
        return Cache::has(Config::get('simple-commerce.cart.key'));
    }

    public function makeCart(): CartRepository
    {
        $cart = Cart::make()
            ->site($this->guessSiteFromRequest())
            ->save();

        Cache::put(config('simple-commerce.cart.key'), $cart->id);

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
        Cache::forget(config('simple-commerce.cart.key'));
    }

    protected function guessSiteFromRequest(): ASite
    {
        if ($site = request()->get('site')) {
            return Site::get($site);
        }

        return Site::current();
    }
}
