<?php

namespace DuncanMcClean\SimpleCommerce\Scopes\Filters;

use DuncanMcClean\SimpleCommerce\Orders\OrderStatus as OrderStatusEnum;
use Statamic\Query\Scopes\Filter;

class OrderStatus extends Filter
{
    public $pinned = true;

    public static function title()
    {
        return __('Status');
    }

    public function fieldItems()
    {
        return [
            'status' => [
                'type' => 'radio',
                'options' => collect(OrderStatusEnum::cases())
                    ->mapWithKeys(fn ($enum) => [$enum->value => OrderStatusEnum::label($enum)])
                    ->all(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->whereStatus(OrderStatusEnum::from($values['status']));
    }

    public function badge($values)
    {
        return OrderStatusEnum::label(OrderStatusEnum::from($values['status']));
    }

    public function visibleTo($key)
    {
        return $key === 'orders';
    }
}
