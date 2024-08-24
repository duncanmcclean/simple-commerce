<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Cart\Cart;
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
        $keys = [
            'id',
            'customer',
        ];

        if ($this->data instanceof Order) {
            $keys = [
                ...$keys,
                'order_number',
                'date',
                'status',
            ];
        }

        return $keys;
    }

    // todo: status
}
