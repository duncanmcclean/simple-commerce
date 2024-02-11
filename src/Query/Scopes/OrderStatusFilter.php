<?php

namespace DuncanMcClean\SimpleCommerce\Query\Scopes;

use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Support\Runway;
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
                'options' => collect(OrderStatus::cases())->mapWithKeys(fn ($case) => [
                    $case->value => __($case->name),
                ])->toArray(),
            ],
        ];
    }

    public function autoApply()
    {
        return [
            'type' => 'placed',
        ];
    }

    public function apply($query, $values)
    {
        return $query->where('order_status', $values['type']);
    }

    public function badge($values)
    {
        $orderStatusLabel = OrderStatus::from($values['type'])->name;

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
