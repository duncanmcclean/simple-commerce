<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Customer
{
    public function findByEmail(string $email): self;

    public function generateTitleAndSlug(): self;
}
