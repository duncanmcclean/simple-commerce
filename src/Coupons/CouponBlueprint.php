<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Customers\EloquentCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as FieldsBlueprint;

class CouponBlueprint
{
    public static function getBlueprint(): FieldsBlueprint
    {
        $customerField = [
            'mode' => 'default',
            'collections' => [
                'customers',
            ],
            'display' => __('Customers'),
            'type' => 'entries',
            'icon' => 'entries',
            'instructions' => __('If selected, this coupon will only be valid for selected customers.'),
            'width' => 50,
        ];

        if (self::isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], UserCustomerRepository::class)) {
            $customerField = [
                'mode' => 'default',
                'display' => __('Customers'),
                'type' => 'users',
                'icon' => 'users',
                'instructions' => __('If selected, this coupon will only be valid for selected customers.'),
                'width' => 50,
            ];
        }

        if (self::isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EloquentCustomerRepository::class)) {
            $customerField = [
                'type' => 'has_many',
                'instructions' => __('If selected, this coupon will only be valid for selected customers.'),
                'display' => __('Customers'),
                'width' => 50,
                'resource' => 'customers',
            ];
        }

        return Blueprint::makeFromTabs([
            'main' => [
                'display' => 'Main',
                'fields' => [
                    'code' => [
                        'type' => 'text',
                        'localizable' => true,
                        'generate' => true,
                        'display' => __('Coupon Code'),
                        'validate' => [
                            'required',
                        ],
                        'listable' => true,
                    ],
                    'description' => [
                        'type' => 'textarea',
                        'instructions' => __('Give yourself a reminder of what this coupon is for.'),
                        'display' => __('Description'),
                        'listable' => true,
                    ],
                    'type' => [
                        'options' => [
                            'percentage' => __('Percentage Discount'),
                            'fixed' => __('Fixed Discount'),
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
                        'listable' => true,
                        'max_items' => 1,
                    ],
                    'value' => [
                        'type' => 'coupon_value',
                        'display' => __('Value'),
                        'width' => 50,
                        'validate' => [
                            'required',
                        ],
                        'listable' => true,
                    ],
                    'optional_settings' => [
                        'type' => 'section',
                        'display' => __('Optional Settings'),
                        'listable' => 'hidden',
                    ],
                    'maximum_uses' => [
                        'input_type' => 'text',
                        'type' => 'text',
                        'instructions' => __('If set, this coupon will only be able to be used a certain amount of times.'),
                        'width' => 50,
                        'display' => __('Maximum Uses'),
                        'listable' => 'hidden',
                    ],
                    'minimum_cart_value' => [
                        'read_only' => false,
                        'type' => 'money',
                        'instructions' => __("What's the minimum items total a cart should have before this coupon can be redeemed?"),
                        'width' => 50,
                        'display' => __('Minimum Cart Value'),
                        'listable' => 'hidden',
                    ],
                    'products' => [
                        'mode' => 'default',
                        'collections' => [
                            config('simple-commerce.content.products.collection', 'product'),
                        ],
                        'display' => __('Products'),
                        'type' => 'entries',
                        'icon' => 'entries',
                        'width' => 50,
                        'instructions' => __('If selected, this coupon will only be valid when any of the products are present.'),
                        'listable' => 'hidden',
                    ],
                    'customers' => $customerField,
                    'expires_at' => [
                        'type' => 'date',
                        'display' => __('Expires At'),
                        'instructions' => __('If defined, this coupon will no longer be redeemable after the expiry date.'),
                        'width' => 50,
                        'listable' => 'hidden',
                    ],
                ],
            ],

            'sidebar' => [
                'display' => 'Sidebar',
                'fields' => [
                    'enabled' => [
                        'display' => __('Enabled?'),
                        'type' => 'toggle',
                        'instructions' => __('When disabled, this coupon will not be redeemable.'),
                        'default' => true,
                        'listable' => 'hidden',
                    ],
                    'redeemed' => [
                        'type' => 'integer',
                        'instructions' => __('Amount of times this coupon has been redeemed.'),
                        'display' => __('Redeemed'),
                        'read_only' => true,
                        'default' => 0,
                        'listable' => 'hidden',
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
