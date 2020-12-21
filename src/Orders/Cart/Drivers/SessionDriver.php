<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderAPI;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as ASite;

class SessionDriver implements CartDriver
{
    public function getCartKey(): string
    {
        return Session::get(Config::get('simple-commerce.cart.key'));
    }

    public function getCart(): Order
    {
        return OrderAPI::find($this->getCartKey());
    }

    public function hasCart(): bool
    {
        return Session::has(Config::get('simple-commerce.cart.key'));
    }

    public function makeCart(): Order
    {
        $cart = OrderAPI::create()
            ->site($this->guessSiteFromRequest())
            ->save();

        Session::put(config('simple-commerce.cart.key'), $cart->id);

        return $cart;
    }

    protected function makeSessionCart(): Order
    {
        if ($this->hasCart()) {
            return $this->getCart();
        }

        return $this->makeCart();
    }

    public function forgetCart()
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
