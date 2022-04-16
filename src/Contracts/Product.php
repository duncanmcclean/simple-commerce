<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use DoubleThreeDigital\SimpleCommerce\Products\ProductVariant;
use Illuminate\Support\Collection;

interface Product
{
    public function id($id = null);

    public function price($price = null);

    public function productVariants($productVariants = null);

    public function stock($stock = null);

    public function taxCategory($taxCategory = null);

    public function resource($resource = null);

    public function purchasableType(): ProductType;

    public function variantOptions(): Collection;

    public function variant(string $optionKey): ?ProductVariant;

    public function beforeSaved();

    public function afterSaved();

    public function save(): self;

    public function delete(): void;

    public function fresh(): self;

    public function toResource();

    public function toAugmentedArray($keys = null);

    public function data($data = null);

    public function has(string $key): bool;

    public function get(string $key, $default = null);

    public function set(string $key, $value): self;

    public function merge($data): self;

    public function toArray(): array;
}
