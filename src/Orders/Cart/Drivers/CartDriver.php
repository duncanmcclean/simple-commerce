<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver as CartDriverContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository;

trait CartDriver
{
    protected function getCartKey(): string
    {
        return resolve(CartDriverContract::class)->getCartKey();
    }

    protected function getCart(): CartRepository
    {
        return resolve(CartDriverContract::class)->getCart();
    }

    protected function hasCart(): bool
    {
        return resolve(CartDriverContract::class)->hasCart();
    }

    protected function makeCart(): CartRepository
    {
        return resolve(CartDriverContract::class)->makeCart();
    }

    protected function getOrMakeCart(): CartRepository
    {
        return resolve(CartDriverContract::class)->getOrMakeCart();
    }

    protected function forgetCart()
    {
        return resolve(CartDriverContract::class)->forgetCart();
    }
}
