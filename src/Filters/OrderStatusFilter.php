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
                    'refunded' => 'Refunded',
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

        if ($values['type'] === 'refunded') {
            return $query
                ->where('is_paid', true)
                ->where('is_refunded', true);
        }
    }

    public function badge($values)
    {
        $orderStatusLabel = $this->fieldItems()['type']['options'][$values['type']];

        return "Order Status: {$orderStatusLabel}";
    }

    public function visibleTo($key)
    {
        if (isset(SimpleCommerce::orderDriver()['collection'])) {
            return $key === 'entries'
                && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $orderModelClass = SimpleCommerce::orderDriver()['model'];
            $runwayResource = \DoubleThreeDigital\Runway\Runway::findResourceByModel(new $orderModelClass);

            return $key === "runway_{$runwayResource->handle()}";
        }

        return false;
    }
}
