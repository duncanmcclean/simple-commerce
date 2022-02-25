<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Events\EntryBlueprintFound;

class AddHiddenFields
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! $event->entry) {
            return $event->blueprint;
        }

        if (
            isset(SimpleCommerce::couponDriver()['collection'])
            && SimpleCommerce::couponDriver()['collection'] === $event->entry->collectionHandle()
        ) {
            return $this->addCouponFields($event);
        }

        if (
            isset(SimpleCommerce::customerDriver()['collection'])
            && SimpleCommerce::customerDriver()['collection'] === $event->entry->collectionHandle()
        ) {
            return $this->addCustomerFields($event);
        }

        if (
            isset(SimpleCommerce::orderDriver()['collection'])
            && SimpleCommerce::orderDriver()['collection'] === $event->entry->collectionHandle()
        ) {
            return $this->addOrderFields($event);
        }

        if (
            isset(SimpleCommerce::productDriver()['collection'])
            && SimpleCommerce::productDriver()['collection'] === $event->entry->collectionHandle()
        ) {
            return $this->addProductFields($event);
        }

        return $event->blueprint;
    }

    protected function addCouponFields(EntryBlueprintFound $event)
    {
        return $event->blueprint;
    }

    protected function addCustomerFields(EntryBlueprintFound $event)
    {
        return $event->blueprint;
    }

    protected function addOrderFields(EntryBlueprintFound $event)
    {
        $event->blueprint->ensureField('receipt_url', [
            'type'    => 'receipt_url',
            'display' => 'SC Receipt URL',
        ], 'sidebar');

        $event->blueprint->ensureField('items', [
            'mode' => 'stacked',
            'reorderable' => false,
            'type' => 'grid',
            'listable' => false,
            'display' => 'Line Items',
            'min_rows' => 1,
            'add_row' => 'Add Line Item',
            'fields' => [
                // Normal fields
                [
                    'handle' => 'id',
                    'field' => [
                        'type' => 'hidden',
                    ],
                ],
                [
                    'handle' => 'product',
                    'field' => [
                        'max_items' => 1,
                        'mode' => 'default',
                        'collections' => [SimpleCommerce::productDriver()['collection']],
                        'type' => 'entries',
                        'display' => 'Product',
                        'validate' => 'required',
                        'width' => 50,
                        'read_only' => true,
                    ],
                ],
                [
                    'handle' => 'variant',
                    'field' => [
                        'display' => 'Variant',
                        'type' => 'product_variant',
                        'width' => 50,
                        'read_only' => true,
                    ],
                ],
                [
                    'handle' => 'quantity',
                    'field' => [
                        'display' => 'Quantity',
                        'type' => 'text',
                        'input_type' => 'number',
                        'validate' => 'required',
                        'width' => 50,
                        'read_only' => true,
                    ],
                ],
                [
                    'handle' => 'total',
                    'field' => [
                        'type' => 'money',
                        'read_only' => true,
                        'display' => 'Total',
                        'width' => 50,
                        'validate' => 'required',
                    ],
                ],
                [
                    'handle' => 'metadata',
                    'field' => [
                        'mode' => 'dynamic',
                        'display' => 'Metadata',
                        'type' => 'array',
                    ],
                ],

                // Hidden/ghost fields
                [
                    'handle' => 'tax',
                    'field' => [
                        'type' => 'simple-commerce::line-item-tax',
                    ],
                ],
            ],
        ]);

        return $event->blueprint;
    }

    protected function addProductFields(EntryBlueprintFound $event)
    {
        return $event->blueprint;
    }
}
