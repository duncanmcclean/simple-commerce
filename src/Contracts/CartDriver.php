<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface CartDriver
{
    public function getSessionCartKey(): string;

    public function getSessionCart(): CartRepository;

    public function hasSessionCart(): bool;

    public function makeSessionCart(): CartRepository;

    public function getOrMakeSessionCart(): CartRepository;

    public function forgetSessionCart();
}
