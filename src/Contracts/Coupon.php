<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Coupon
{
    public function id($id = null);

    public function code($code = null);

    public function value($value = null);

    public function type($type = null);

    public function resource($resource = null);

    public function isValid(Order $order): bool;

    public function redeem(): self;

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
