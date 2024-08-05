<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Site;

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


        return $this->cachedKeys = $this->data->data()->keys()
            ->merge($this->commonKeys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'order_number',
            'status',
            'customer',
            'line_items',
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

    public function lineItems()
    {
        // TODO: Refactor into an AugmentedLineItem class
        return $this->data->lineItems()->map(function ($lineItem) {
            return [];

            return array_merge($lineItem->toArray(), [
                'product' => $lineItem->product()->toAugmentedArray(),
            ]);
        });
    }
}
