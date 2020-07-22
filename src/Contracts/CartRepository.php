<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Statamic\Entries\Entry;

interface CartRepository
{
    public function make(): self;
    public function find(string $id): self;
    public function save(): self;
    public function update(array $data, bool $mergeData = true): self;
    public function items(array $items = []): self;
    public function customer(string $customer = ''): self;
    public function count(): int;
    public function entry(): Entry;
    public function redeemCoupon(string $code): bool;
    public function markAsCompleted(): self;
    public function buildReceipt(): string;
    public function calculateTotals(): self;
}
