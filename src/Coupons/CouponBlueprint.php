<?php

namespace DuncanMcClean\SimpleCommerce\Coupons;

use DuncanMcClean\SimpleCommerce\Customers\EloquentCustomerRepository;
use DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as FieldsBlueprint;

class CouponBlueprint
{
    public static function getBlueprint(): FieldsBlueprint
    {
        $customerField = [
            'mode' => 'default',
            'collections' => [
                config('simple-commerce.content.customers.collection', 'customers'),
            ],
            'display' => __('Specific Customers'),
            'type' => 'entries',
            'icon' => 'entries',
            'if' => [
                'customer_eligibility' => 'specific_customers',
            ],
        ];

        if (self::isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], UserCustomerRepository::class)) {
            $customerField = [
                'mode' => 'default',
                'display' => __('Specific Customers'),
                'type' => 'users',
                'icon' => 'users',
                'if' => [
                    'customer_eligibility' => 'specific_customers',
                ],
            ];
        }

        if (self::isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EloquentCustomerRepository::class)) {
            $customerField = [
                'type' => 'has_many',
                'display' => __('Specific Customers'),
                'resource' => 'customers',
                'if' => [
                    'customer_eligibility' => 'specific_customers',
                ],
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
                                        'instructions' => __('Customers will enter this code to redeem the coupon.'),
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
                            ],
                        ],
                        [
                            'display' => __('Options'),
                            'fields' => [
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
                                        'validate' => ['required'],
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
                                        'validate' => ['required'],
                                        'listable' => true,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Minimum Requirements'),
                            'fields' => [
                                [
                                    'handle' => 'minimum_cart_value',
                                    'field' => [
                                        'type' => 'money',
                                        'instructions' => __("The minimum value the customer's cart should have before this coupon can be redeemed."),
                                        'width' => 50,
                                        'display' => __('Minimum Order Value'),
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Customer Eligibility'),
                            'fields' => [
                                [
                                    'handle' => 'customer_eligibility',
                                    'field' => [
                                        'options' => [
                                            'all' => __('All'),
                                            'specific_customers' => __('Specific customers'),
                                            'customers_by_domain' => __('Specific customers (by domain)'),
                                        ],
                                        'inline' => false,
                                        'type' => 'radio',
                                        'display' => __('Which customers are eligible for this coupon?'),
                                        'validate' => ['required'],
                                        'default' => 'all',
                                    ],
                                ],
                                [
                                    'handle' => 'customers',
                                    'field' => $customerField,
                                ],
                                [
                                    'handle' => 'customers_by_domain',
                                    'field' => [
                                        'type' => 'list',
                                        'display' => __('Domains'),
                                        'instructions' => __('Provide a list of domains that are eligible for this coupon. One per line.'),
                                        'add_button' => __('Add Domain'),
                                        'if' => [
                                            'customer_eligibility' => 'customers_by_domain',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Usage Limits'),
                            'fields' => [
                                [
                                    'handle' => 'maximum_uses',
                                    'field' => [
                                        'type' => 'integer',
                                        'width' => 50,
                                        'display' => __('Maximum times coupon can be redeemed'),
                                        'instructions' => __('By default, coupons can be redeemed an unlimited amount of times. You can set a maximum here if you wish.'),
                                        'listable' => 'hidden',
                                    ],
                                ],
                                [
                                    'handle' => 'products',
                                    'field' => [
                                        'mode' => 'default',
                                        'collections' => [
                                            config('simple-commerce.content.products.collection', 'products'),
                                        ],
                                        'display' => __('Limit to certain products'),
                                        'instructions' => __('This coupon will only be redeemable when *any* of these products are present in the order.'),
                                        'type' => 'entries',
                                        'icon' => 'entries',
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Active Dates'),
                            'instructions' => __('Configure when this coupon is active. Leave both dates blank to make the coupon active indefinitely.'),
                            'fields' => [
                                [
                                    'handle' => 'valid_from',
                                    'field' => [
                                        'type' => 'date',
                                        'display' => __('Start Date'),
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ],
                                [
                                    'handle' => 'expires_at',
                                    'field' => [
                                        'type' => 'date',
                                        'display' => __('End Date'),
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sidebar' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'summary', 'field' => ['type' => 'coupon_summary']],
                            ],
                        ],
                        [
                            'fields' => [
                                [
                                    'handle' => 'redeemed',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('Redemptions'),
                                        'instructions' => __('The number of times this coupon has been redeemed.'),
                                        'visibility' => 'read_only',
                                        'default' => 0,
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
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
