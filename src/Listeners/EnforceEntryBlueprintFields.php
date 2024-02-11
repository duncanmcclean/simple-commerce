<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Customers\EntryCustomerRepository;
use DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\AssetContainer;
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
        $event->blueprint->ensureField('product_type', [
            'type' => 'button_group',
            'display' => __('Product Type'),
            'options' => [
                'physical' => __('Physical'),
                'digital' => __('Digital'),
            ],
            'default' => 'physical',
        ], 'sidebar', true);

        if (! $event->blueprint->hasField('product_variants')) {
            $event->blueprint->ensureField('price', [
                'type' => 'money',
                'display' => __('Price'),
                'save_zero_value' => true,
            ], 'sidebar');
        }

        if ($event->blueprint->hasField('product_variants')) {
            $productVariantsField = $event->blueprint->field('product_variants');

            $hasDigitalProductFields = collect($productVariantsField->config()['option_fields'] ?? [])
                ->filter(function ($value, $key) {
                    return $value['handle'] === 'download_limit'
                        || $value['handle'] === 'downloadable_asset';
                })
                ->count() > 0;

            if (! $hasDigitalProductFields) {
                $event->blueprint->ensureFieldHasConfig(
                    'product_variants',
                    array_merge(
                        $productVariantsField->toArray(),
                        [
                            'option_fields' => array_merge(
                                $productVariantsField->get('option_fields', []),
                                collect($this->getDigitalProductFields())
                                    ->map(function ($value, $key) {
                                        return [
                                            'handle' => $key,
                                            'field' => $value,
                                        ];
                                    })
                                    ->values()
                                    ->toArray()
                            ),
                        ]
                    )
                );
            }

            return $event->blueprint;
        } else {
            collect($this->getDigitalProductFields())
                ->reject(fn ($value, $key) => $event->blueprint->hasField($key))
                ->each(function ($value, $key) use (&$event) {
                    $event->blueprint->ensureFieldInTab($key, $value, 'sidebar');
                });
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

    protected function getDigitalProductFields(): array
    {
        return [
            'downloadable_asset' => [
                'type' => 'assets',
                'mode' => 'grid',
                'display' => __('Downloadable Asset'),
                'container' => AssetContainer::all()->first()?->handle(),
                'if' => [
                    'root.product_type' => 'equals digital',
                ],
            ],
            'download_limit' => [
                'type' => 'integer',
                'display' => __('Download Limit'),
                'instructions' => __("If you'd like to limit the amount if times this product can be downloaded, set it here. Keep it blank if you'd like it to be unlimited."),
                'if' => [
                    'root.product_type' => 'equals digital',
                ],
            ],
        ];
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

        $event->blueprint->ensureField('status_log', [
            'type' => 'sc_status_log',
            'display' => 'Status Log',
            'listable' => 'hidden',
            'hide_display' => true,
        ], 'sidebar');

        $event->blueprint->ensureField('order_date', [
            'type' => 'date',
            'display' => 'Order Date',
            'mode' => 'single',
            'time_enabled' => false,
            'listable' => true,
            'visibility' => 'read_only',
        ], 'sidebar');

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

        return $event->blueprint;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
