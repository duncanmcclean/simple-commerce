<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface ProductRepository
{
    public function all();

    public function find($id): ?Product;

    public function make(): Product;

    public function save(Product $product): void;

    public function delete(Product $product): void;

    public static function bindings(): array;
}
