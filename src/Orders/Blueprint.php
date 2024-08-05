<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Fields\Blueprint as StatamicBlueprint;

class Blueprint
{
    public function __invoke(): StatamicBlueprint
    {
        return BlueprintFacade::make()->setContents(array_merge_recursive([
            'tabs' => [
                'details' => [
                    'display' => __('Details'),
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'order_number',
                                    'field' => ['type' => 'text', 'display' => __('Order Number'), 'visibility' => 'read_only', 'listable' => true],
                                ],
                                [
                                    'handle' => 'line_items',
                                    'field' => ['type' => 'line_items', 'display' => __('Line Items'), 'visibility' => 'hidden', 'listable' => false],
                                ],
                                [
                                    'handle' => 'grand_total',
                                    'field' => ['type' => 'money', 'display' => __('Grand Total'), 'visibility' => 'hidden', 'listable' => true],
                                ],
                                [
                                    'handle' => 'sub_total',
                                    'field' => ['type' => 'money', 'display' => __('Subtotal'), 'visibility' => 'hidden', 'listable' => true],
                                ],
                                [
                                    'handle' => 'discount_total',
                                    'field' => ['type' => 'money', 'display' => __('Discount Total'), 'visibility' => 'hidden', 'listable' => true],
                                ],
                                [
                                    'handle' => 'shipping_total',
                                    'field' => ['type' => 'money', 'display' => __('Shipping Total'), 'visibility' => 'hidden', 'listable' => true],
                                ],
                                [
                                    'handle' => 'tax_total',
                                    'field' => ['type' => 'money', 'display' => __('Tax Total'), 'visibility' => 'hidden', 'listable' => true],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Receipt'),
                            'fields' => [
                                // TODO: Receipt field - this might not even need to be a field
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
                                // TODO: Special shipping field
                                [
                                    'handle' => 'shipping_method',
                                    'field' => ['type' => 'text', 'display' => __('Shipping Method'), 'visibility' => 'read_only', 'listable' => true],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Shipping Address'),
                            'fields' => [
                                [
                                    'handle' => 'shipping_line_1',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 1'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_line_2',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 2'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_city',
                                    'field' => ['type' => 'text', 'display' => __('Town/City'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_postcode',
                                    'field' => ['type' => 'text', 'display' => __('Postcode'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50],
                                ],
                                [
                                    'handle' => 'shipping_country',
                                    'field' => ['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 1, 'display' => __('Country'), 'listable' => false, 'visibility' => 'read_only', 'width' => 50],
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
                                // TODO: Special payment field
                                [
                                    'handle' => 'payment_gateway',
                                    'field' => ['type' => 'text', 'display' => __('Payment Gateway'), 'visibility' => 'read_only', 'listable' => true],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Billing Address'),
                            'fields' => [
                                [
                                    'handle' => 'use_shipping_address_for_billing',
                                    'field' => ['type' => 'toggle', 'display' => __('Use Shipping Address for Billing'), 'visibility' => 'read_only', 'listable' => false, 'validate' => 'boolean'],
                                ],
                                [
                                    'handle' => 'billing_line_1',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 1'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_line_2',
                                    'field' => ['type' => 'text', 'display' => __('Address Line 2'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_city',
                                    'field' => ['type' => 'text', 'display' => __('Town/City'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_postcode',
                                    'field' => ['type' => 'text', 'display' => __('Postcode'), 'visibility' => 'read_only', 'listable' => false, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
                                ],
                                [
                                    'handle' => 'billing_country',
                                    'field' => ['type' => 'dictionary', 'dictionary' => 'countries', 'display' => __('Country'), 'listable' => false, 'visibility' => 'read_only', 'max_items' => 1, 'width' => 50, 'if' => ['use_shipping_address_for_billing' => 'equals false']],
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
                                // TODO: Customer field
                            ],
                        ],
                    ],
                ],
            ],
        ], BlueprintFacade::find('simple-commerce::order')->contents()));
    }
}
