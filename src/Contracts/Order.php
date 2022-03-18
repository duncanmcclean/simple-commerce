<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Illuminate\Support\Collection;

interface Order
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

    public function data($data = null);

    public function has(string $key): bool;

    public function get(string $key, $default = null);

    public function set(string $key, $value);

    public function toArray(): array;

    public function billingAddress();

    public function shippingAddress();

    public function customer($customer = null);

    public function coupon($coupon = null);

    public function gateway();

    public function redeemCoupon(string $code): bool;

    public function markAsPaid(): self;

    public function markAsShipped(): self;

    public function receiptUrl(): string;

    public function recalculate(): self;

    public function rules(): array;

    public function withoutRecalculating(callable $callback);

    public function lineItems(): Collection;

    public function lineItem($lineItemId): array;

    public function addLineItem(array $lineItemData): array;

    public function updateLineItem($lineItemId, array $lineItemData): array;

    public function removeLineItem($lineItemId): Collection;

    public function clearLineItems(): Collection;

    public static function bindings(): array;
}
