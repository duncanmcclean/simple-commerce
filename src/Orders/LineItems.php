<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use Illuminate\Support\Collection;

trait LineItems
{
    public function lineItems($lineItems = null)
    {
        return $this
            ->fluentlyGetOrSet('lineItems')
            ->getter(function ($value) {
                if ($value === null) {
                    $value = [];
                }

                if (! $value instanceof Collection) {
                    $value = collect($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function lineItem($lineItemId): array
    {
        return $this->lineItems()->firstWhere('id', $lineItemId);
    }

    public function addLineItem(array $lineItemData): array
    {
        $lineItemData['id'] = app('stache')->generateId();

        $this->lineItems[] = $lineItemData;

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItemData['id']);
    }

    public function updateLineItem($lineItemId, array $lineItemData): array
    {
        $this->lineItems = $this->lineItems()->map(function ($item) use ($lineItemId, $lineItemData) {
            if ($item['id'] !== $lineItemId) {
                return $item;
            }

            return array_merge($item, $lineItemData);
        })->toArray();

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItemId);
    }

    public function removeLineItem($lineItemId): Collection
    {
        $this->lineItems = $this->lineItems()->reject(function ($item) use ($lineItemId) {
            return $item['id'] === $lineItemId;
        })->toArray();

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItems();
    }

    public function clearLineItems(): Collection
    {
        $this->lineItems = [];

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItems();
    }
}
