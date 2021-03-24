<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Coupon
{
    public function all();

    public function query();

    public function find($id): self;

    public function create(array $data = [], string $site = ''): self;

    public function save(): self;

    public function delete();

    public function toResource();

    public function id();

    public function title(string $title = null);

    public function slug(string $slug = null);

    public function site($site = null): self;

    public function fresh(): self;

    public function data(array $data = []);

    public function has(string $key): bool;

    public function get(string $key);

    public function set(string $key, $value);

    public function toArray(): array;

    public function findByCode(string $code): self;

    public function isValid(Order $order): bool;

    public function redeem(): self;

    public static function bindings(): array;
}
