<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Support\Arr;
use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Fields\Blueprint as StatamicBlueprint;

class Blueprint
{
    public function __invoke(): StatamicBlueprint
    {
        $contents = [
            'tabs' => [
                'details' => [
                    'display' => __('Details'),
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'status',
                                    'field' => ['type' => 'order_status', 'display' => __('Order Status'), 'visibility' => 'hidden', 'listable' => true],
                                ],
                                [
                                    'handle' => 'order_number',
                                    'field' => ['type' => 'text', 'display' => __('Order Number'), 'visibility' => 'hidden', 'listable' => true],
                                ],
                                [
                                    'handle' => 'line_items',
                                    'field' => ['type' => 'line_items', 'display' => __('Line Items'), 'visibility' => 'hidden', 'listable' => 'hidden', 'sortable' => false],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Receipt'),
                            'fields' => [
                                [
                                    'handle' => 'receipt',
                                    'field' => ['type' => 'order_receipt', 'hide_display' => true, 'listable' => false],
                                ],
                            ],
                        ],
                    ],
                ],
                'shipping' => [
                    'display' => __('Shipping'),
                    'sections' => [
                        [
                            'display' => __('Shipping Option'),
                            'fields' => [
                                [
                                    'handle' => 'shipping_details',
                                    'field' => ['type' => 'shipping_details', 'hide_display' => true, 'listable' => false],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Shipping Address'),
                            'fields' => [
                                [
                                    'handle' => 'shipping_line_1',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 1'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_line_2',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 2'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_city',
                                    'field' => ['type' => 'text', 'display' => __('Town/City'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_postcode',
                                    'field' => ['type' => 'text', 'display' => __('Postcode'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_country',
                                    'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'countries', 'emojis' => false], 'max_items' => 1, 'display' => __('Country'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_state',
                                    'field' => ['type' => 'state', 'from' => 'shipping_country', 'display' => __('State'), 'listable' => false, 'max_items' => 1, 'width' => 50],
                                ],
                            ],
                        ],
                    ],
                ],
                'payment' => [
                    'display' => __('Payment'),
                    'sections' => [
                        [
                            'display' => __('Payment'),
                            'fields' => [
                                [
                                    'handle' => 'payment_details',
                                    'field' => ['type' => 'payment_details', 'hide_display' => true, 'listable' => false],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Billing Address'),
                            'fields' => [
                                [
                                    'handle' => 'billing_line_1',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 1'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'billing_line_2',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 2'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'billing_city',
                                    'field' => ['type' => 'text', 'display' => __('Town/City'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'billing_postcode',
                                    'field' => ['type' => 'text', 'display' => __('Postcode'), 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'billing_country',
                                    'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'countries', 'emojis' => false], 'display' => __('Country'), 'listable' => false, 'max_items' => 1, 'width' => 50],
                                ],
                                [
                                    'handle' => 'billing_state',
                                    'field' => ['type' => 'state', 'from' => 'billing_country', 'display' => __('State/County'), 'listable' => false, 'max_items' => 1, 'width' => 50],
                                ],
                            ],
                        ],
                    ],
                ],
                'sidebar' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'date',
                                    'field' => ['type' => 'date', 'display' => __('Date'), 'visibility' => 'read_only', 'listable' => true, 'time_enabled' => true],
                                ],
                                [
                                    'handle' => 'customer',
                                    'field' => ['type' => 'customer', 'display' => __('Customer'), 'listable' => true],
                                ],
                                [
                                    'handle' => 'grand_total',
                                    'field' => ['type' => 'money', 'display' => __('Grand Total'), 'visibility' => 'hidden', 'listable' => true, 'save_zero_value' => true],
                                ],
                                [
                                    'handle' => 'sub_total',
                                    'field' => ['type' => 'money', 'display' => __('Subtotal'), 'visibility' => 'hidden', 'listable' => 'hidden', 'save_zero_value' => true],
                                ],
                                [
                                    'handle' => 'discount_total',
                                    'field' => ['type' => 'money', 'display' => __('Coupon Total'), 'visibility' => 'hidden', 'listable' => 'hidden', 'save_zero_value' => true],
                                ],
                                [
                                    'handle' => 'shipping_total',
                                    'field' => ['type' => 'money', 'display' => __('Shipping Total'), 'visibility' => 'hidden', 'listable' => 'hidden', 'save_zero_value' => true],
                                ],
                                [
                                    'handle' => 'tax_total',
                                    'field' => ['type' => 'money', 'display' => __('Tax Total'), 'visibility' => 'hidden', 'listable' => 'hidden', 'save_zero_value' => true],
                                ],
                                [
                                    'handle' => 'coupon',
                                    'field' => ['type' => 'coupon', 'display' => __('Coupon'), 'visibility' => 'hidden', 'listable' => 'hidden', 'max_items' => 1],
                                ],
                                [
                                    'handle' => 'payment_gateway',
                                    'field' => ['type' => 'payment_gateway', 'display' => __('Payment Gateway'), 'visibility' => 'hidden', 'listable' => 'hidden', 'max_items' => 1],
                                ],
                                [
                                    'handle' => 'shipping_method',
                                    'field' => ['type' => 'shipping_method', 'display' => __('Shipping Method'), 'visibility' => 'hidden', 'listable' => 'hidden', 'max_items' => 1],
                                ],
                                [
                                    'handle' => 'shipping_option',
                                    'field' => ['type' => 'shipping_option', 'display' => __('Shipping Option'), 'visibility' => 'hidden', 'listable' => 'hidden', 'max_items' => 1],
                                ],
                                [
                                    'handle' => 'tracking_number',
                                    'field' => ['type' => 'text', 'display' => __('Tracking Number'), 'visibility' => 'hidden', 'listable' => 'hidden'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $customBlueprint = BlueprintFacade::find('simple-commerce::order');

        foreach (Arr::get($customBlueprint->contents(), 'tabs') as $tabHandle => $tab) {
            if (isset($contents['tabs'][$tabHandle])) {
                // Merge fields in existing sections.
                $sections = array_map(function ($section) use ($tab): array {
                    $fields = $section['fields'];
                    $display = $section['display'] ?? null;

                    collect($tab['sections'])
                        ->filter(fn ($section) => $section['display'] === $display)
                        ->each(function ($customSection) use (&$fields): void {
                            $fields = [
                                ...$fields,
                                ...$customSection['fields'],
                            ];
                        });

                    return ['display' => $display, 'fields' => $fields];
                }, $contents['tabs'][$tabHandle]['sections']);

                // Merge new sections.
                collect($tab['sections'])
                    ->reject(fn($section) => collect($sections)->contains('display', $section['display']))
                    ->each(function ($section) use (&$sections): void {
                        $sections[] = $section;
                    });

                $contents['tabs'][$tabHandle]['sections'] = $sections;

                continue;
            }

            $contents['tabs'][$tabHandle] = $tab;
        }

        return BlueprintFacade::make()
            ->setHandle('orders')
            ->setContents($contents);
    }
}
