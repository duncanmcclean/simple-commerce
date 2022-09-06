<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use Statamic\Facades\Blueprint;

class CouponBlueprint
{
    public static function getBlueprint()
    {
        return Blueprint::makeFromSections([
            'main' => [
                'display' => 'Main',
                'fields' => [
                    'code' => [
                        'type' => 'text',
                        'localizable' => true,
                        'generate' => true,
                        'display' => 'Coupon Code',
                        'validate' => [
                            'required',
                        ],
                    ],
                    'description' => [
                        'type' => 'textarea',
                        'instructions' => 'Give yourself a reminder of what this coupon is for.',
                        'display' => 'Description',
                    ],
                    'type' => [
                        'options' => [
                            'percentage' => 'Percentage Discount',
                            'fixed' => 'Fixed Discount',
                        ],
                        'clearable' => false,
                        'multiple' => false,
                        'searchable' => false,
                        'taggable' => false,
                        'push_tags' => false,
                        'cast_booleans' => false,
                        'type' => 'select',
                        'display' => 'Type',
                        'width' => 50,
                        'validate' => [
                            'required',
                        ],
                    ],
                    'value' => [
                        'input_type' => 'text',
                        'type' => 'text',
                        'display' => 'Value',
                        'width' => 50,
                        'validate' => [
                            'required',
                        ],
                    ],
                    'optional_settings' => [
                        'type' => 'section',
                        'display' => 'Optional Settings',
                    ],
                    'maximum_uses' => [
                        'input_type' => 'text',
                        'type' => 'text',
                        'instructions' => 'If set, this coupon will only be able to be used a certain amount of times.',
                        'width' => 50,
                        'display' => 'Maximum Uses',
                    ],
                    'minimum_cart_value' => [
                        'read_only' => false,
                        'type' => 'money',
                        'instructions' => "What's the minimum items total a cart should have before this coupon can be redeemed?",
                        'width' => 50,
                        'display' => 'Minimum Cart Value',
                    ],
                    'products' => [
                        'mode' => 'default',
                        'collections' => [
                            config('simple-commerce.content.products.collection', 'product'),
                        ],
                        'display' => 'Products',
                        'type' => 'entries',
                        'icon' => 'entries',
                        'width' => 50,
                        'instructions' => 'If selected, this coupon will only be valid when any of the products are present.',
                    ],
                    'customers' => [
                        'mode' => 'default',
                        'collections' => [
                            'customers',
                        ],
                        'display' => 'Customers',
                        'type' => 'entries',
                        'icon' => 'entries',
                        'instructions' => 'If selected, this coupon will only be valid for selected customers.',
                        'width' => 50,
                    ],
                ],
            ],

            'sidebar' => [
                'display' => 'Sidebar',
                'fields' => [
                    'redeemed' => [
                        'input_type' => 'number',
                        'type' => 'text',
                        'instructions' => 'Amount of times this coupon has been redeemed.',
                        'display' => 'Redeemed',
                        'read_only' => true,
                        'default' => 0,
                    ],
                ],
            ],
        ]);
    }
}
