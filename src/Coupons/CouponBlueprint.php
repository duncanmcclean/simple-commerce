<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Customers\EloquentCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Blueprint;

class CouponBlueprint
{
    public static function getBlueprint()
    {
        $customerField = [
            'mode' => 'default',
            'collections' => [
                'customers',
            ],
            'display' => 'Customers',
            'type' => 'entries',
            'icon' => 'entries',
            'instructions' => 'If selected, this coupon will only be valid for selected customers.',
            'width' => 50,
        ];

        if (self::isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], UserCustomerRepository::class)) {
            $customerField = [
                'mode' => 'default',
                'display' => 'Customers',
                'type' => 'users',
                'icon' => 'users',
                'instructions' => 'If selected, this coupon will only be valid for selected customers.',
                'width' => 50,
            ];
        }

        if (self::isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EloquentCustomerRepository::class)) {
            $customerField = [
                'type' => 'has_many',
                'instructions' => 'If selected, this coupon will only be valid for selected customers.',
                'display' => 'Customers',
                'width' => 50,
            ];
        }

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
                    'customers' => $customerField,
                ],
            ],

            'sidebar' => [
                'display' => 'Sidebar',
                'fields' => [
                    'enabled' => [
                        'display' => 'Enabled?',
                        'type' => 'toggle',
                        'instructions' => 'When disabled, this coupon will not be redeemable.',
                        'default' => true,
                    ],
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

    protected static function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
