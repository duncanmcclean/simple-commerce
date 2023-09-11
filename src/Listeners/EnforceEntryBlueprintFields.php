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
            && "collections.{$customerDriver['collection']}" === $event->blueprint->namespace()
        ) {
            return $this->enforceCustomerFields($event);
        }

        if (isset($productDriver['collection']) && "collections.{$productDriver['collection']}" === $event->blueprint->namespace()) {
            return $this->enforceProductFields($event);
        }

        if (isset($orderDriver['collection']) && "collections.{$orderDriver['collection']}" === $event->blueprint->namespace()) {
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
                'type' => 'money',
                'display' => __('Price'),
                'save_zero_value' => true,
            ], 'sidebar');
        }

        if (SimpleCommerce::isUsingStandardTaxEngine()) {
            $event->blueprint->ensureField('tax_category', [
                'type' => 'tax_category',
                'display' => __('Tax Category'),
                'max_items' => 1,
                'mode' => 'select',
            ], 'sidebar');
        }

        return $event->blueprint;
    }

    protected function enforceOrderFields($event): Blueprint
    {
        $event->blueprint->ensureField('grand_total', [
            'type' => 'money',
            'display' => __('Grand Total'),
            'read_only' => true,
            'validate' => ['required'],
            'save_zero_value' => true,
        ]);

        $event->blueprint->ensureField('items_total', [
            'type' => 'money',
            'display' => __('Items Total'),
            'read_only' => true,
            'validate' => ['required'],
            'save_zero_value' => true,
        ]);

        $event->blueprint->ensureField('shipping_total', [
            'type' => 'money',
            'display' => __('Shipping Total'),
            'read_only' => true,
            'validate' => ['required'],
            'save_zero_value' => true,
        ]);

        $event->blueprint->ensureField('tax_total', [
            'type' => 'money',
            'display' => __('Tax Total'),
            'read_only' => true,
            'validate' => ['required'],
            'save_zero_value' => true,
        ]);

        $event->blueprint->ensureField('coupon_total', [
            'type' => 'money',
            'display' => __('Coupon Total'),
            'read_only' => true,
            'validate' => ['required'],
            'save_zero_value' => true,
        ]);

        $event->blueprint->ensureField('order_status', [
            'type' => 'order_status',
            'display' => 'Order Status',
            'read_only' => true,
            'validate' => 'required',
        ], 'sidebar');

        $event->blueprint->ensureField('payment_status', [
            'type' => 'payment_status',
            'display' => 'Payment Status',
            'read_only' => true,
            'validate' => 'required',
        ], 'sidebar');

        $event->blueprint->ensureField('status_log', [
            'type' => 'sc_status_log',
            'display' => 'Status Log',
        ], 'sidebar');

        return $event->blueprint;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
