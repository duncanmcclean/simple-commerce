<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver as CartDriverContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository;

trait CartDriver
{
    protected function getSessionCartKey(): string
    {
        return resolve(CartDriverContract::class)->getSessionCartKey();
    }

    protected function getSessionCart(): CartRepository
    {
        return resolve(CartDriverContract::class)->getSessionCart();
    }

    protected function hasSessionCart(): bool
    {
        return resolve(CartDriverContract::class)->hasSessionCart();
    }

    protected function makeSessionCart(): CartRepository
    {
        return resolve(CartDriverContract::class)->makeSessionCart();
    }

    protected function getOrMakeSessionCart(): CartRepository
    {
        return resolve(CartDriverContract::class)->getOrMakeSessionCart();
    }

    protected function forgetSessionCart()
    {
        return resolve(CartDriverContract::class)->forgetSessionCart();
    }
}
