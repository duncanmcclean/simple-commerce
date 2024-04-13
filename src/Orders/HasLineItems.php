<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Support\Collection;

trait HasLineItems
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

                return $value->map(function ($item) {
                    if ($item instanceof LineItem) {
                        return $item;
                    }

                    if (! isset($item['id'])) {
                        $item['id'] = app('stache')->generateId();
                    }

                    if (! isset($item['total'])) {
                        $item['total'] = 0;
                    }

                    $lineItem = (new LineItem($item))
                        ->id($item['id'])
                        ->product($item['product'])
                        ->quantity($item['quantity'])
                        ->total($item['total']);

                    if (isset($item['variant'])) {
                        $lineItem->variant($item['variant']);
                    }

                    if (isset($item['tax'])) {
                        $lineItem->tax($item['tax']);
                    }

                    if (isset($item['metadata'])) {
                        $lineItem->metadata($item['metadata']);
                    }

                    // If the line item's product has been deleted, remove
                    // it from the cart & return null.
                    if ($this->paymentStatus() !== PaymentStatus::Paid && ! $lineItem->product()) {
                        return null;
                    }

                    return $lineItem;
                })->filter();
            })
            ->args(func_get_args());
    }

    public function lineItem($lineItemId): ?LineItem
    {
        return $this->lineItems()->firstWhere('id', $lineItemId);
    }

    public function addLineItem(array $lineItemData): LineItem
    {
        $lineItem = (new LineItem)
            ->id(app('stache')->generateId())
            ->product($lineItemData['product'])
            ->quantity($lineItemData['quantity'])
            ->total($lineItemData['total']);

        if (isset($lineItemData['variant'])) {
            $lineItem->variant($lineItemData['variant']);
        }

        if (isset($lineItemData['tax'])) {
            $lineItem->tax($lineItemData['tax']);
        }

        if (isset($lineItemData['metadata'])) {
            $lineItem->metadata($lineItemData['metadata']);
        }

        $this->lineItems = $this->lineItems->push($lineItem)->values();

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItem->id());
    }

    public function updateLineItem($lineItemId, array $lineItemData): LineItem
    {
        $this->lineItems = $this->lineItems->map(function ($item) use ($lineItemId, $lineItemData) {
            if ($lineItemId !== $item->id()) {
                return $item;
            }

            $lineItem = $item;

            if (isset($lineItemData['product'])) {
                $lineItem->product($lineItemData['product']);
            }

            if (isset($lineItemData['quantity'])) {
                $lineItem->quantity($lineItemData['quantity']);
            }

            if (isset($lineItemData['total'])) {
                $lineItem->total($lineItemData['total']);
            }

            if (isset($lineItemData['variant'])) {
                $lineItem->variant($lineItemData['variant']);
            }

            if (isset($lineItemData['tax'])) {
                $lineItem->tax($lineItemData['tax']);
            }

            if (isset($lineItemData['metadata'])) {
                $lineItem->metadata($lineItemData['metadata']);
            }

            return $lineItem;
        })->values();

        $this->save();

        if (! $this->withoutRecalculating) {
            $this->recalculate();
        }

        return $this->lineItem($lineItemId);
    }

    public function removeLineItem($lineItemId): Collection
    {
        $this->lineItems = $this->lineItems->reject(function ($item) use ($lineItemId) {
            return $lineItemId === $item->id();
        })->values();

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
