<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Illuminate\Support\Collection;
use Statamic\Entries\Entry;

interface CouponRepository
{
    public function make(): self;
    public function all(): Collection;
    public function find(string $id): self;
    public function update(array $data): self;
    public function entry(): Entry;
    public function isValid(Entry $order): bool;
}