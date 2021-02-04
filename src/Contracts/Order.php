<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Statamic\Http\Resources\API\EntryResource;

interface Order
{
    public function all();

    public function query();

    public function find(string $id): self;

    public function create(array $data = [], string $site = ''): self;

    public function save(): self;

    public function delete();

    public function toResource(): EntryResource;

    public function id();

    public function title(string $title = '');

    public function slug(string $slug = '');

    public function site($site = null): self;

    public function fresh(): self;

    public function data(array $data = []);

    public function has(string $key): bool;

    public function get(string $key);

    public function set(string $key, $value);

    public function toArray(): array;

    public function billingAddress();

    public function shippingAddress();

    public function customer(string $customer = '');

    public function coupon(string $coupon = '');

    public function redeemCoupon(string $code): bool;

    public function markAsCompleted(): self;

    public function boolReceipt(): string;

    public function calculateTotals(): self;

    public static function bindings(): array;
}
