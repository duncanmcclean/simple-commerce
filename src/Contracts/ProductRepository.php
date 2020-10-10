<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Statamic\Entries\Entry;

interface ProductRepository
{
    public function make(): self;

    public function find(string $id): self;

    public function data(array $data = []);

    public function save(): self;

    public function update(array $data, bool $mergeData = true): self;

    public function entry(): Entry;

    public function toArray(): array;

    public function stockCount();

    public function purchasableType(): string;

    public function variantOption(string $optionKey): ?array;
}
