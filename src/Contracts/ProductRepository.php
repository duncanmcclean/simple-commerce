<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

interface ProductRepository
{
    public function all();

    public function query();

    public function find($id): ?Product;

    public function findOrFail($id): Product;

    public function make(): Product;

    public function save(Product $product): void;

    public function delete(Product $product): void;

    public static function bindings(): array;
}
