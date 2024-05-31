<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Support\Collection;

trait HasLineItems
{
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
