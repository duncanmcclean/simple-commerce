<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface CartDriver
{
    public function getCartKey(): string;

    public function getCart(): CartRepository;

    public function hasCart(): bool;

    public function makeCart(): CartRepository;

    public function getOrMakeCart(): CartRepository;

    public function forgetCart();
}
