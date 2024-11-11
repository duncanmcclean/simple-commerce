<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Site;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Support\Str;

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
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'id',
            'total_including_tax',
            'total_excluding_tax',
        ];
    }

    public function get($handle): Value
    {
        // These fields have methods on the LineItem class. However, we don't want to call those methods,
        // we want to use the underlying properties.
        if (in_array($handle, ['product', 'variant', 'quantity', 'unit_price', 'total'])) {
            $value = new Value(
                fn () => $this->data->{Str::camel($handle)},
                $handle,
                $this->blueprintFields()->get($handle)?->fieldtype(),
                $this->data
            );

            return $value->resolve();
        }

        return parent::get($handle);
    }

    public function totalIncludingTax(): int
    {
        return $this->data->total() + Arr::get($this->data->data()->get('tax'), 'amount', 0);
    }

    public function totalExcludingTax(): int
    {
        return $this->data->total();
    }
}
