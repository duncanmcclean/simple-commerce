<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Fields\Blueprint;

class EnforceEntryBlueprintFields
{
    public function handle(EntryBlueprintFound $event)
    {
        $customerDriver = SimpleCommerce::customerDriver();
        $productDriver = SimpleCommerce::productDriver();
        $orderDriver = SimpleCommerce::orderDriver();

        if (
            $this->isOrExtendsClass($customerDriver['repository'], EntryCustomerRepository::class)
            && $event->blueprint->namespace() === "collections.{$customerDriver['collection']}"
        ) {
            return $this->enforceCustomerFields($event);
        }

        if (isset($productDriver['collection']) && $event->blueprint->namespace() === "collections.{$productDriver['collection']}") {
            return $this->enforceProductFields($event);
        }

        if (isset($orderDriver['collection']) && $event->blueprint->namespace() === "collections.{$orderDriver['collection']}") {
            return $this->enforceOrderFields($event);
        }
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

    protected function enforceProductFields($event): Blueprint
    {
        if (! $event->blueprint->hasField('product_variants')) {
            $event->blueprint->ensureField('price', [
                'type'    => 'money',
                'display' => __('Price'),
            ], 'sidebar');
        }

        if (SimpleCommerce::isUsingStandardTaxEngine()) {
            $event->blueprint->ensureField('tax_category', [
                'type'      => 'tax_category',
                'display'   => __('Tax Category'),
                'max_items' => 1,
                'mode'      => 'select',
            ], 'sidebar');
        }

        return $event->blueprint;
    }

    protected function enforceOrderFields($event): Blueprint
    {
        $event->blueprint->ensureField('grand_total', [
            'type'      => 'money',
            'display'   => __('Grand Total'),
            'read_only' => true,
            'validate'  => ['required'],
        ]);

        $event->blueprint->ensureField('items_total', [
            'type'      => 'money',
            'display'   => __('Items Total'),
            'read_only' => true,
            'validate'  => ['required'],
        ]);

        $event->blueprint->ensureField('shipping_total', [
            'type'      => 'money',
            'display'   => __('Shipping Total'),
            'read_only' => true,
            'validate'  => ['required'],
        ]);

        $event->blueprint->ensureField('tax_total', [
            'type'      => 'money',
            'display'   => __('Tax Total'),
            'read_only' => true,
            'validate'  => ['required'],
        ]);

        $event->blueprint->ensureField('coupon_total', [
            'type'      => 'money',
            'display'   => __('Coupon Total'),
            'read_only' => true,
            'validate'  => ['required'],
        ]);

        return $event->blueprint;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
