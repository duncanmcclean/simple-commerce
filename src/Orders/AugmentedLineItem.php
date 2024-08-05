<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Site;
use Statamic\Support\Arr;

class AugmentedLineItem extends AbstractAugmented
{
    private $cachedKeys;

    public function keys(): array
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = $this->data->data()->keys()
//            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'id',
            'product',
            'variant',
            'quantity',
            'total',
            'total_including_tax',
        ];
    }

    public function product()
    {
        return $this->data->product()->toAugmentedCollection();
    }

    public function variant()
    {
        //
    }

    public function total()
    {
        return Money::format($this->data->total(), Site::current());
    }

    public function totalIncludingTax(): int
    {
        return $this->data->total() + Arr::get($this->data->data()->get('tax'), 'amount', 0);
    }
}
