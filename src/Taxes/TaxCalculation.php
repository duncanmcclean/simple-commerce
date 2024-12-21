<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxCalculation implements Arrayable
{
    use FluentlyGetsAndSets;

    public $rate;
    public $description;
    public $zone;
    public $amount;

    public static function make($rate, $description, $zone, $amount): self
    {
        return (new self)
            ->rate($rate)
            ->description($description)
            ->zone($zone)
            ->amount($amount);
    }

    public function rate($rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function description($description): self
    {
        $this->description = $description;

        return $this;
    }

    public function zone($zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function amount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'rate' => $this->rate,
            'description' => $this->description,
            'zone' => $this->zone,
            'amount' => $this->amount,
        ];
    }
}
