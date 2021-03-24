<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Product
{
    public function all();

    public function query();

    public function find($id): self;

    public function create(array $data = [], string $site = ''): self;

    public function save(): self;

    public function delete();

    public function toResource();

    public function id();

    public function title(string $title = '');

    public function slug(string $slug = '');

    public function site($site = null): self;

    public function fresh(): self;

    public function data(array $data = []);

    public function has(string $key): bool;

    public function get(string $key);

    public function set(string $key, $value);

    public function toArray(): array;

    public function stockCount();

    public function purchasableType(): string;

    public function variantOption(string $optionKey): ?array;

    public function isExemptFromTax(): bool;

    public static function bindings(): array;
}
