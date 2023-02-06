<?php

namespace DoubleThreeDigital\SimpleCommerce\Scopes;

use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Runway;
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
                    'cart' => __('Cart'),
                    'paid' => __('Paid'),
                    'shipped' => __('Shipped'),
                    'refunded' => __('Refunded'),
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

        return __('Order Status: :orderStatus', ['orderStatus' => $orderStatusLabel]);
    }

    public function visibleTo($key)
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $key === 'entries'
                && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $runwayResource = Runway::orderModel();

            return $key === "runway_{$runwayResource->handle()}";
        }

        return false;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
