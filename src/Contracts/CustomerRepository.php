<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface CustomerRepository
{
    public function make(): self;
    public function find(string $id): self;
    public function findByEmail(string $email): self;
    public function data(array $data = []): self;
    public function save(): self;
    public function update(array $data, bool $mergeData = true): self;
    public function entry();
    public function toArray(): array
}