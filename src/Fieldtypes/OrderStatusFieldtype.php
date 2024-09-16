<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Statamic\Fields\Fieldtype;

class OrderStatusFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcessIndex($data)
    {
        return [
            'value' => $data,
            'label' => OrderStatus::label($data)
        ];
    }

    public function preload()
    {
        return [
            'options' => collect(OrderStatus::cases())->map(fn ($status) => [
                'value' => $status,
                'label' => OrderStatus::label($status)
            ])->values(),
        ];
    }
}