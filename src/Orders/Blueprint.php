<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Fields\Blueprint as StatamicBlueprint;

class Blueprint
{
    public function __invoke(): StatamicBlueprint
    {
        return BlueprintFacade::make()->setHandle('orders')->setContents(array_merge_recursive([
            'tabs' => [
                'details' => [
                    'display' => __('Details'),
                    'sections' => [
                        [
                            'fields' => [
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
                                ]
                            ],
                        ]
                    ],
                ],
                'shipping' => [
                    'display' => __('Shipping'),
                    'sections' => [
                        [
                            'display' => __('Shipping Method'),
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
                                    'field' => ['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 1, 'display' => __('Country'), 'listable' => false, 'width' => 50],
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
                                    'handle' => 'use_shipping_address_for_billing',
                                    'field' => ['type' => 'toggle', 'display' => __('Use Shipping Address for Billing'), 'listable' => false, 'validate' => 'boolean'],
                                ],
                                [
                                    'handle' => 'billing_line_1',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 1'), 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_line_2',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 2'), 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_city',
                                    'field' => ['type' => 'text', 'display' => __('Town/City'), 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_postcode',
                                    'field' => ['type' => 'text', 'display' => __('Postcode'), 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_country',
                                    'field' => ['type' => 'dictionary', 'dictionary' => 'countries', 'display' => __('Country'), 'listable' => false, 'max_items' => 1, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
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
                                    'field' => ['type' => 'money', 'display' => __('Discount Total'), 'visibility' => 'hidden', 'listable' => 'hidden', 'save_zero_value' => true],
                                ],
                                [
                                    'handle' => 'shipping_total',
                                    'field' => ['type' => 'money', 'display' => __('Shipping Total'), 'visibility' => 'hidden', 'listable' => 'hidden', 'save_zero_value' => true],
                                ],
                                [
                                    'handle' => 'tax_total',
                                    'field' => ['type' => 'money', 'display' => __('Tax Total'), 'visibility' => 'hidden', 'listable' => 'hidden', 'save_zero_value' => true],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], BlueprintFacade::find('simple-commerce::order')->contents()));
    }
}
