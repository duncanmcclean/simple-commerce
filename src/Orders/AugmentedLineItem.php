<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Statamic\Data\AbstractAugmented;
use Statamic\Fields\Value;
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
        ];
    }

    public function get($handle): Value
    {
        // These fields have methods on the LineItem class. However, we don't want to call those methods,
        // we want to use the underlying properties.
        if (in_array($handle, ['product', 'quantity', 'unit_price', 'sub_total', 'tax_total', 'total'])) {
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
}
