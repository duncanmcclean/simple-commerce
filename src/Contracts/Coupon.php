<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Coupon
{
    public function id($id = null);

    public function code($code = null);

    public function value($value = null);

    public function type($type = null);

    public function isValid(Order $order): bool;

    public function redeem(): self;

    public function beforeSaved();

    public function afterSaved();

    public function save(): self;

    public function delete(): void;

    public function fresh(): self;

    public function toResource();

    public function toAugmentedArray($keys = null);

    public function toArray(): array;
}
