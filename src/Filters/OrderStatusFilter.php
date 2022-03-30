<?php

namespace DoubleThreeDigital\SimpleCommerce\Filters;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Query\Scopes\Filter;

class OrderStatusFilter extends Filter
{
    public $pinned = true;
    public static $title = 'Order Status';

    public function fieldItems()
    {
        return [
            'type' => [
                'type' => 'radio',
                'options' => [
                    'cart' => 'Cart',
                    'paid' => 'Paid',
                    'shipped' => 'Shipped',
                ],
            ],
        ];
    }

    public function autoApply()
    {
        return [
            'type' => 'paid',
        ];
    }

    public function apply($query, $values)
    {
        if ($values['type'] === 'cart') {
            return $query
                ->where('is_paid', false);
        }

        if ($values['type'] === 'paid') {
            return $query
                ->where('is_paid', true)
                ->where('is_shipped', false);
        }

        if ($values['type'] === 'shipped') {
            return $query
                ->where('is_paid', true)
                ->where('is_shipped', true);
        }
    }

    public function badge($values)
    {
        $orderStatusLabel = $this->fieldItems()['type']['options'][$values['type']];

        return "Order Status: {$orderStatusLabel}";
    }

    public function visibleTo($key)
    {
        return $key === 'entries'
            && isset(SimpleCommerce::orderDriver()['driver'])
            && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
    }
}
