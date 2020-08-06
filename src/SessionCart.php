<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;

trait SessionCart
{
    protected function getSessionCartKey(): string
    {
        return Session::get(Config::get('simple-commerce.cart_key'));
    }

    protected function getSessionCart(): CartRepository
    {
        return Cart::find($this->getSessionCartKey());
    }

    protected function hasSessionCart(): bool
    {
        return Session::has(Config::get('simple-commerce.cart_key'));
    }

    protected function makeSessionCart(): CartRepository
    {
        $cart = Cart::make()->save();

        Session::put(config('simple-commerce.cart_key'), $cart->id);

        return $cart;
    }
}
