<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Data\Address;
use Statamic\Entries\Entry;
use Statamic\Http\Resources\API\EntryResource;

interface CartRepository
{
    public function make(): self;

    public function find(string $id): self;

    public function data(array $data = []);

    public function save(): self;

    public function update(array $data, bool $mergeData = true): self;

    public function entry(): Entry;

    public function toArray(): array;

    public function toResource(): EntryResource;

    public function billingAddress(): ?Address;

    public function shippingAddress(): ?Address;

    public function customer(): CustomerRepository;

    public function coupon(): CouponRepository;

    public function redeemCoupon(string $code): bool;

    public function markAsCompleted(): self;

    public function buildReceipt(): string;

    public function calculateTotals(): self;
}
