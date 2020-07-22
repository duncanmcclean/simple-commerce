<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Illuminate\Support\Collection;
use Statamic\Entries\Entry;

interface CouponRepository
{
    public function make(): self;
    public function find(string $id): self;
    public function findByCode(string $code): self;
    public function data(array $data = []): self;
    public function save(): self;
    public function update(array $data, bool $mergeData = true): self;
    public function entry(): Entry;
    public function toArray(): array;
    public function isValid(Entry $order): bool;
}
