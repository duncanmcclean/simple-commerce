<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Statamic\Entries\Entry;

interface Coupon
{
    public function findByCode(string $code): self;

    public function isValid(Entry $order): bool;

    public function redeem(): self;
}
