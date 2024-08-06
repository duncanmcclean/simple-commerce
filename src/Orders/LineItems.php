<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Facades\Stache;

class LineItems extends Collection
{
    public function create(array $data): self
    {
        $product = Product::find(Arr::pull($data, 'product'));

        $lineItem = (new LineItem)
            ->id(Arr::pull($data, 'id', Stache::generateId()))
            ->product($product)
            ->variant(Arr::pull($data, 'variant'))
            ->quantity(Arr::pull($data, 'quantity'))
            ->unitPrice(Arr::pull($data, 'unit_price', $product->price()))
            ->total(Arr::pull($data, 'total', 0))
            ->data(collect($data));

        $this->push($lineItem);

        return $this;
    }

    public function find(string $id): ?LineItem
    {
        return $this->first(fn (LineItem $lineItem) => $lineItem->id() === $id);
    }

    public function update(string $id, array $data): self
    {
        $lineItem = $this->find($id);

        $product = Product::find(Arr::pull($data, 'product', $lineItem->product()->id()));

        $lineItem
            ->product($product)
            ->quantity(Arr::pull($data, 'quantity', $lineItem->quantity()))
            ->total(Arr::pull($data, 'total', $lineItem->total()))
            ->variant(Arr::pull($data, 'variant', $lineItem->variant()))
            ->data(collect($data));

        return $this;
    }

    public function remove(string $id): self
    {
        $this->items = $this->reject(fn (LineItem $lineItem) => $lineItem->id() === $id)->values()->all();

        return $this;
    }
}
