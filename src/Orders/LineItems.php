<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use Illuminate\Support\Collection;

trait LineItems
{
    public function lineItems($lineItems = null)
    {
        return $this
            ->fluentlyGetOrSet('lineItems')
            ->setter(function ($value) {
                if ($value === null) {
                    $value = collect();
                }

                if (is_array($value)) {
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

        $this->lineItems = $this->lineItems->push($lineItemData);

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItemData['id']);
    }

    public function updateLineItem($lineItemId, array $lineItemData): array
    {
        $this->lineItems = $this->lineItems->map(function ($item) use ($lineItemId, $lineItemData) {
            if ($item['id'] !== $lineItemId) {
                return $item;
            }

            return array_merge($item, $lineItemData);
        });

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItemId);
    }

    public function removeLineItem($lineItemId): Collection
    {
        $this->lineItems = $this->lineItems->reject(function ($item) use ($lineItemId) {
            return $item['id'] === $lineItemId;
        });

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItems();
    }

    public function clearLineItems(): Collection
    {
        $this->lineItems = collect();

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItems();
    }
}
