<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository;
use DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Events\UserBlueprintFound;
use Statamic\Fields\Blueprint;

class EnforceUserBlueprintFields
{
    public function handle(UserBlueprintFound $event)
    {
        $customerDriver = SimpleCommerce::customerDriver();

        if ($this->isOrExtendsClass($customerDriver['repository'], UserCustomerRepository::class)) {
            return $this->enforceCustomerFields($event);
        }

        return $event->blueprint;
    }

    protected function enforceCustomerFields($event): Blueprint
    {
        $orderDriver = SimpleCommerce::orderDriver();

        if ($this->isOrExtendsClass($orderDriver['repository'], EntryOrderRepository::class)) {
            if (! $event->blueprint->hasField('orders')) {
                $event->blueprint->ensureField('orders', [
                    'type' => 'entries',
                    'display' => __('Orders'),
                    'mode' => 'default',
                    'collections' => [
                        $orderDriver['collection'],
                    ],
                    'create' => false,
                ]);
            }
        }

        if ($this->isOrExtendsClass($orderDriver['repository'], EloquentOrderRepository::class)) {
            if (! $event->blueprint->hasField('orders')) {
                $event->blueprint->ensureField('orders', [
                    'type' => 'has_many',
                    'display' => __('Orders'),
                    'mode' => 'default',
                    'resource' => 'orders',
                    'create' => false,
                ]);
            }
        }

        return $event->blueprint;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
