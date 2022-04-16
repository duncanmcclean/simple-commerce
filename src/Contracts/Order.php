<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use Illuminate\Support\Collection;

interface Order
{
    public function id($id = null);

    public function orderNumber($orderNumber = null);

    public function isPaid($isPaid = null);

    public function isShipped($isShipped = null);

    public function isRefunded($isRefunded = null);

    public function grandTotal($grandTotal = null);

    public function itemsTotal($itemsTotal = null);

    public function taxTotal($taxTotal = null);

    public function shippingTotal($shippingTotal = null);

    public function couponTotal($couponTotal = null);

    public function customer($customer = null);

    public function coupon($coupon = null);

    public function gateway($gateway = null);

    public function currentGateway(): ?array;

    public function resource($resource = null);

    public function billingAddress(): ?Address;

    public function shippingAddress(): ?Address;

    public function redeemCoupon(string $code): bool;

    public function markAsPaid(): self;

    public function markAsShipped(): self;

    public function refund($refundData): self;

    public function recalculate(): self;

    public function withoutRecalculating(callable $callback);

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

    public function lineItems($lineItems = null);

    public function lineItem($lineItemId): LineItem;

    public function addLineItem(array $lineItemData): LineItem;

    public function updateLineItem($lineItemId, array $lineItemData): LineItem;

    public function removeLineItem($lineItemId): Collection;

    public function clearLineItems(): Collection;
}
