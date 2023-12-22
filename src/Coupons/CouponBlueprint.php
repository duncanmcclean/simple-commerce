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

        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'code',
                                    'field' => [
                                        'type' => 'coupon_code',
                                        'display' => __('Coupon Code'),
                                        'validate' => ['required'],
                                        'listable' => true,
                                        'instructions' => __('Customers will use this code to redeem the coupon.'),
                                    ],
                                ],
                                [
                                    'handle' => 'description',
                                    'field' => [
                                        'type' => 'textarea',
                                        'instructions' => __('Give yourself a reminder of what this coupon is for.'),
                                        'display' => __('Description'),
                                        'listable' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'type',
                                    'field' => [
                                        'type' => 'select',
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
                                ],
                                [
                                    'handle' => 'value',
                                    'field' => [
                                        'type' => 'coupon_value',
                                        'display' => __('Value'),
                                        'width' => 50,
                                        'validate' => [
                                            'required',
                                        ],
                                        'listable' => true,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Optional Settings'),
                            'fields' => [
                                [
                                    'handle' => 'maximum_uses',
                                    'field' => [
                                        'type' => 'integer',
                                        'instructions' => __('If set, this coupon will only be able to be used a certain amount of times.'),
                                        'width' => 50,
                                        'display' => __('Maximum Uses'),
                                        'listable' => 'hidden',
                                    ],
                                ],
                                [
                                    'handle' => 'minimum_cart_value',
                                    'field' => [
                                        'type' => 'money',
                                        'instructions' => __("What's the minimum items total a cart should have before this coupon can be redeemed?"),
                                        'width' => 50,
                                        'display' => __('Minimum Cart Value'),
                                        'listable' => 'hidden',
                                    ],
                                ],
                                [
                                    'handle' => 'products',
                                    'field' => [
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
                                ],
                                [
                                    'handle' => 'customers',
                                    'field' => $customerField,
                                ],
                                [
                                    'handle' => 'expires_at',
                                    'field' => [
                                        'type' => 'date',
                                        'display' => __('Expires At'),
                                        'instructions' => __('If defined, this coupon will no longer be redeemable after the expiry date.'),
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ]
                            ],
                        ]
                    ],
                ],
                'sidebar' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'enabled',
                                    'field' => [
                                        'type' => 'toggle',
                                        'instructions' => __('When disabled, this coupon will not be redeemable.'),
                                        'default' => true,
                                        'listable' => 'hidden',
                                        'display' => __('Enabled?'),
                                    ],
                                ],
                                [
                                    'handle' => 'redeemed',
                                    'field' => [
                                        'type' => 'integer',
                                        'instructions' => __('Amount of times this coupon has been redeemed.'),
                                        'display' => __('Redeemed'),
                                        'read_only' => true,
                                        'default' => 0,
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ]
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
