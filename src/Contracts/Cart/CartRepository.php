<?php

namespace DuncanMcClean\SimpleCommerce\Contracts\Cart;

interface CartRepository
{
    public function all();

    public function query();

    public function find($id): ?Cart;

    public function findOrFail($id): Cart;

    public function current(): ?Cart;

    public function hasCurrentCart(): bool;

    public function forgetCurrentCart(): void;

    public function make(): Cart;

    public function save(Cart $cart): void;

    public function delete(Cart $cart): void;

    public static function bindings(): array;
}
