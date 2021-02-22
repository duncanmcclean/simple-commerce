<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use Illuminate\Support\Collection;

trait OrderItems
{
    public function orderItems(): Collection
    {
        if (!$this->has('items')) {
            return collect([]);
        }

        return collect($this->get('items'));
    }

    public function orderItem(string $orderItemId): array
    {
        return $this->orderItems()
            ->firstWhere('id', $orderItemId);
    }

    public function addOrderItem(array $orderItemData): array
    {
        $orderItemData['id'] = app('stache')->generateId();

        $this->data([
            'items' => array_merge($this->orderItems()->toArray(), [$orderItemData]),
        ]);

        $this->save();
        $this->calculateTotals();

        return $this->orderItem($orderItemData['id']);
    }

    public function updateOrderItem(string $orderItemId, array $orderItemData): array
    {
        $this->data([
            'items' => $this->orderItems()
                ->map(function ($item) use ($orderItemId, $orderItemData) {
                    if ($item['id'] !== $orderItemId) {
                        return $item;
                    }

                    return array_merge($item, $orderItemData);
                })
                ->toArray(),
        ]);

        $this->save();
        $this->calculateTotals();

        return $this->orderItem($orderItemId);
    }

    public function removeOrderItem(string $orderItemId): Collection
    {
        $this->data([
            'items' => $this->orderItems()
                ->reject(function ($item) use ($orderItemId) {
                    return $item['id'] === $orderItemId;
                })
                ->toArray(),
        ]);

        $this->save();
        $this->calculateTotals();

        return $this->orderItems();
    }

    public function clearOrderItems(): Collection
    {
        $this->data([
            'items' => [],
        ]);

        $this->save();
        $this->calculateTotals();

        return $this->orderItems();
    }
}
