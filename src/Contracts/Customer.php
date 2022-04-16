<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Illuminate\Support\Collection;

interface Customer
{
    public function id($id = null);

    public function resource($resource = null);

    public function name(): ?string;

    public function email($email = null);

    public function orders(): Collection;

    public function routeNotificationForMail($notification = null);

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
}
