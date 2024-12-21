<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Statamic\Fields\Fieldtype;

class OrderStatusFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcessIndex($data)
    {
        if (! $data) {
            return null;
        }

        if (! $data instanceof OrderStatus) {
            $data = OrderStatus::from($data);
        }

        return [
            'value' => $data,
            'label' => OrderStatus::label($data),
        ];
    }

    public function preload()
    {
        return [
            'options' => collect(OrderStatus::cases())
                ->when(! $this->field()->parent()?->shippingMethod(), function ($collection) {
                    return $collection->reject(OrderStatus::Shipped);
                })
                ->map(fn ($status) => [
                    'value' => $status,
                    'label' => OrderStatus::label($status),
                ])
                ->values(),
        ];
    }
}
