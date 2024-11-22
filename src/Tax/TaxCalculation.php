<?php

namespace DuncanMcClean\SimpleCommerce\Tax;

class TaxCalculation
{
    public function __construct(
        protected int $amount = 0,
        protected int|float $rate = 0,
        protected bool $priceIncludesTax = false
    ) {}

    public function amount(): int
    {
        return $this->amount;
    }

    public function rate(): int|float
    {
        return $this->rate;
    }

    public function priceIncludesTax(): bool
    {
        return $this->priceIncludesTax;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'rate' => $this->rate,
            'price_includes_tax' => $this->priceIncludesTax,
        ];
    }
}
