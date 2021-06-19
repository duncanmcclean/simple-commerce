<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax;

class TaxCalculation
{
    protected $amount;
    protected $rate;
    protected $priceIncludesTax;

    public function __construct(int $amount = 0, float $rate = 0, bool $priceIncludesTax = false)
    {
        $this->amount = $amount;
        $this->rate = $rate;
        $this->priceIncludesTax = $priceIncludesTax;
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function rate(): float
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
            'amount'             => $this->amount,
            'rate'               => $this->rate,
            'price_includes_tax' => $this->priceIncludesTax,
        ];
    }
}
