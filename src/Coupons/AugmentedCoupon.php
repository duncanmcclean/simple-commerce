<?php

namespace DuncanMcClean\SimpleCommerce\Coupons;

use Statamic\Data\AbstractAugmented;

class AugmentedCoupon extends AbstractAugmented
{
    private $cachedKeys;

    public function keys(): array
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = collect()
            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
//            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'id',
            'code',
            'type',
            'amount',
            'discount_text',
        ];
    }
}
