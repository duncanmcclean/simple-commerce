<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Customer
{
    public function all();

    public function query();

    public function find($id): self;

    public function create(array $data = [], string $site = ''): self;

    public function save(): self;

    public function delete();

    public function toResource();

    public function toAugmentedArray($keys = null);

    public function id();

    public function title(string $title = null);

    public function slug(string $slug = null);

    public function site($site = null);

    public function fresh(): self;

    public function data(array $data = []);

    public function has(string $key): bool;

    public function get(string $key);

    public function set(string $key, $value);

    public function toArray(): array;

    public function findByEmail(string $email): self;

    public function name(): string;

    public function email(): string;

    public static function bindings(): array;
}
