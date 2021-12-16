<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Fields\Blueprint;

class EnforceBlueprintFields
{
    public function handle(EntryBlueprintFound $event)
    {
        $productDriver = SimpleCommerce::productDriver();
        $orderDriver = SimpleCommerce::orderDriver();

        if (isset($productDriver['collection']) && $event->blueprint->namespace() === "collections.{$productDriver['collection']}") {
            return $this->enforceProductFields($event);
        }

        if (isset($orderDriver['collection']) && $event->blueprint->namespace() === "collections.{$orderDriver['collection']}") {
            return $this->enforceOrderFields($event);
        }
    }

    protected function enforceProductFields($event): Blueprint
    {
        if (! $event->blueprint->hasField('product_variants')) {
            $event->blueprint->ensureField('price', [
                'type'    => 'money',
                'display' => 'Price',
            ], 'sidebar');
        }

        if (SimpleCommerce::isUsingStandardTaxEngine()) {
            $event->blueprint->ensureField('tax_category', [
                'type'      => 'tax_category',
                'display'   => 'Tax Category',
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
            'display'   => 'Grand Total',
            'read_only' => true,
            'validate'  => 'required',
        ]);

        $event->blueprint->ensureField('items_total', [
            'type'      => 'money',
            'display'   => 'Items Total',
            'read_only' => true,
            'validate'  => 'required',
        ]);

        $event->blueprint->ensureField('shipping_total', [
            'type'      => 'money',
            'display'   => 'Shipping Total',
            'read_only' => true,
            'validate'  => 'required',
        ]);

        $event->blueprint->ensureField('tax_total', [
            'type'      => 'money',
            'display'   => 'Tax Total',
            'read_only' => true,
            'validate'  => 'required',
        ]);

        $event->blueprint->ensureField('coupon_total', [
            'type'      => 'money',
            'display'   => 'Coupon Total',
            'read_only' => true,
            'validate'  => 'required',
        ]);

        return $event->blueprint;
    }
}
