<?php

namespace DuncanMcClean\SimpleCommerce\Tax;

class TaxCalculation
{
    public function __construct(
        protected int $amount = 0,
        protected $rate = 0,
        protected bool $priceIncludesTax = false
    ) {
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function rate()
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
            'amount' => (int) $this->amount,
            'rate' => (int) $this->rate,
            'price_includes_tax' => $this->priceIncludesTax,
        ];
    }
}
