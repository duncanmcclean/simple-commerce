<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use Statamic\Data\AbstractAugmented;

class AugmentedOrder extends AbstractAugmented
{
    private $cachedKeys;

    public function keys(): array
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = $this->data->data()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'id',
            'order_number',
            'status',
            'customer',
        ];
    }

    // todo: status

    public function customer()
    {
        $customer = $this->data->customer();

        if ($customer instanceof GuestCustomer) {
            return $customer->toArray();
        }

        return $customer?->toAugmentedCollection();
    }
}
