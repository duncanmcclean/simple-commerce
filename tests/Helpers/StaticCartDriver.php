<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Helpers;

use DuncanMcClean\SimpleCommerce\Contracts\CartDriver;
use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Order;

class StaticCartDriver implements CartDriver
{
    public static $cart;

    public static function use(): self
    {
        app()->bind(CartDriver::class, static::class);

        return new static();
    }

    public static function setCart(OrderContract $order): self
    {
        static::$cart = $order;

        return new static();
    }

    public function getCartKey(): string
    {
        return static::$cart->id();
    }

    public function getCart(): OrderContract
    {
        if ($this->hasCart()) {
            return static::$cart;
        }

        return $this->makeCart();
    }

    public function hasCart(): bool
    {
        return ! is_null(static::$cart);
    }

    public function makeCart(): OrderContract
    {
        static::$cart = Order::make();
        static::$cart->save();

        return static::$cart;
    }

    public function getOrMakeCart(): OrderContract
    {
        if ($this->hasCart()) {
            return $this->getCart();
        }

        return $this->makeCart();
    }

    public function forgetCart()
    {
        static::$cart = null;
    }
}
