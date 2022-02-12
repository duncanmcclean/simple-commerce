<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use Illuminate\Support\Collection;

trait LineItems
{
    public function lineItems(): Collection
    {
        if (! $this->has('items')) {
            return collect();
        }

        return collect($this->get('items'));
    }

    public function lineItem($lineItemId): array
    {
        return $this->lineItems()
            ->firstWhere('id', $lineItemId);
    }

    public function addLineItem(array $lineItemData): array
    {
        $lineItemData['id'] = app('stache')->generateId();

        $this->set('items', array_merge($this->lineItems()->toArray(), [$lineItemData]));
        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItemData['id']);
    }

    public function updateLineItem($lineItemId, array $lineItemData): array
    {
        $this->set(
            'items',
            $this->lineItems()
                ->map(function ($item) use ($lineItemId, $lineItemData) {
                    if ($item['id'] !== $lineItemId) {
                        return $item;
                    }

                    return array_merge($item, $lineItemData);
                })
                ->toArray()
        );

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItemId);
    }

    public function removeLineItem($lineItemId): Collection
    {
        $this->set(
            'items',
            $this->lineItems()
                ->reject(function ($item) use ($lineItemId) {
                    return $item['id'] === $lineItemId;
                })
                ->toArray()
        );

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItems();
    }

    public function clearLineItems(): Collection
    {
        $this->set('items', []);
        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItems();
    }
}
