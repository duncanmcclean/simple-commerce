<?php

namespace DuncanMcClean\SimpleCommerce\Coupons;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Rules\UniqueCouponValue;
use Statamic\Fields\Blueprint as FieldsBlueprint;
use Statamic\Rules\Handle;

class Blueprint
{
    public function __invoke(): FieldsBlueprint
    {
        return \Statamic\Facades\Blueprint::make()->setContents([
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
                                        'instructions' => __('Customers will enter this code to redeem the coupon.'),
                                        'listable' => true,
                                        'validate' => ['required', 'uppercase', new Handle],
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
                                        'options' => collect(CouponType::cases())
                                            ->mapWithKeys(fn ($enum) => [$enum->value => CouponType::label($enum)])
                                            ->all(),
                                        'clearable' => false,
                                        'multiple' => false,
                                        'searchable' => false,
                                        'taggable' => false,
                                        'push_tags' => false,
                                        'cast_booleans' => false,
                                        'display' => 'Type',
                                        'width' => 50,
                                        'validate' => ['required'],
                                        'listable' => false,
                                        'max_items' => 1,
                                    ],
                                ],
                                [
                                    'handle' => 'amount',
                                    'field' => [
                                        'type' => 'coupon_amount',
                                        'display' => __('Amount'),
                                        'width' => 50,
                                        'validate' => ['required'],
                                        'listable' => false,
                                        'if' => [
                                            // We only want the Amount field to show when a Type has been selected.
                                            'type' => 'contains e',
                                        ],
                                    ],
                                ],
                                [
                                    'handle' => 'discount_text',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Discount'),
                                        'listable' => true,
                                        'visibility' => 'hidden',
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
                                        'listable' => false,
                                    ],
                                ],
                                [
                                    'handle' => 'customers',
                                    'field' => [
                                        'mode' => 'default',
                                        'display' => __('Specific Customers'),
                                        'type' => 'users',
                                        'icon' => 'users',
                                        'if' => [
                                            'customer_eligibility' => 'specific_customers',
                                        ],
                                    ],
                                ],
                                [
                                    'handle' => 'customers_by_domain',
                                    'field' => [
                                        'type' => 'list',
                                        'display' => __('Domains'),
                                        'instructions' => __('Provide a list of domains that are eligible for this coupon. One per line.'),
                                        'add_button' => __('Add Domain'),
                                        'listable' => false,
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
                                        'collections' => config('statamic.simple-commerce.products.collections'),
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
            ],
        ]);
    }
}
