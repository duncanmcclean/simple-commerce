<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

interface CartDriver
{
    public function getCartKey(): string;

    public function getCart(): Order;

    public function hasCart(): bool;

    public function makeCart(): Order;

    public function getOrMakeCart(): Order;

    public function forgetCart();
}
