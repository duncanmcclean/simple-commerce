<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver as CartDriverContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;

trait CartDriver
{
    protected function getCartKey(): string
    {
        return resolve(CartDriverContract::class)->getCartKey();
    }

    protected function getCart(): Order
    {
        try {
            return resolve(CartDriverContract::class)->getCart();
        } catch (OrderNotFound $e) {
            $this->makeCart();

            return $this->getCart();
        }
    }

    protected function hasCart(): bool
    {
        try {
            return resolve(CartDriverContract::class)->getCart();
        } catch (OrderNotFound $e) {
            return false;
        }
    }

    protected function makeCart(): Order
    {
        return resolve(CartDriverContract::class)->makeCart();
    }

    protected function getOrMakeCart(): Order
    {
        return resolve(CartDriverContract::class)->getOrMakeCart();
    }

    protected function forgetCart()
    {
        return resolve(CartDriverContract::class)->forgetCart();
    }
}
