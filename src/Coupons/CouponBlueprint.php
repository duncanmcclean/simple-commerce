<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use Statamic\Facades\Blueprint;

class CouponBlueprint
{
    public static function blueprint()
    {
        return Blueprint::makeFromSections([
            'main' => [
                'display' => 'Main',
                'fields' => [
                    [
                        'handle' => 'description',
                        'field' => [
                            'type' => 'textarea',
                            'required' => true,
                            'instructions' => 'Give yourself a reminder of what this coupon is for.',
                            'listable' => 'hidden',
                            'display' => 'Description',
                            'validate' => [
                                'required',
                            ],
                        ],
                    ],
                    [
                        'handle' => 'type',
                        'field' => [
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
                          'listable' => 'hidden',
                          'display' => 'Type',
                          'validate' => 'required',
                          'width' => 50,
                        ],
                      ],
                      [
                        'handle' => 'coupon_value',
                        'field' => [
                          'input_type' => 'text',
                          'type' => 'text',
                          'listable' => 'hidden',
                          'display' => 'Value',
                          'validate' => 'required',
                          'width' => 50,
                        ],
                      ],
                      [
                        'handle' => 'optional_settings',
                        'field' => [
                          'type' => 'section',
                          'listable' => 'hidden',
                          'display' => 'Optional Settings',
                        ],
                      ],
                      [
                        'handle' => 'maximum_uses',
                        'field' => [
                          'input_type' => 'text',
                          'type' => 'text',
                          'instructions' => 'If set, this coupon will only be able to be used a certain amount of times.',
                          'width' => 50,
                          'listable' => 'hidden',
                          'display' => 'Maximum Uses',
                        ],
                      ],
                      [
                        'handle' => 'minimum_cart_value',
                        'field' => [
                          'read_only' => false,
                          'type' => 'money',
                          'instructions' => "What's the minimum items total a cart should have before this coupon can be redeemed?",
                          'width' => 50,
                          'listable' => 'hidden',
                          'display' => 'Minimum Cart Value',
                        ],
                      ],
                      [
                        'handle' => 'products',
                        'field' => [
                          'mode' => 'default',
                          'collections' => [
                            'products',
                          ],
                          'display' => 'Products',
                          'type' => 'entries',
                          'icon' => 'entries',
                          'listable' => 'hidden',
                          'width' => 50,
                          'instructions' => 'If selected, this coupon will only be valid when any of the products are present.',
                        ],
                      ],
                      [
                        'handle' => 'customers',
                        'field' => [
                          'mode' => 'default',
                          'collections' => [
                            'customers',
                          ],
                          'display' => 'Customers',
                          'type' => 'entries',
                          'icon' => 'entries',
                          'instructions' => 'If selected, this coupon will only be valid for selected customers.',
                          'width' => 50,
                          'listable' => 'hidden',
                        ],
                      ],
                ],
            ],
            'sidebar' => [
                'display' => 'Sidebar',
                'fields' => [
                  [
                    'handle' => 'slug',
                    'field' => [
                      'type' => 'slug',
                      'required' => true,
                      'localizable' => true,
                      'generate' => true,
                      'listable' => 'hidden',
                      'display' => 'Coupon Code',
                      'validate' => [
                        'required',
                      ],
                    ],
                  ],
                  [
                    'handle' => 'redeemed',
                    'field' => [
                      'input_type' => 'number',
                      'type' => 'text',
                      'instructions' => 'Amount of times this coupon has been redeemed.',
                      'listable' => 'hidden',
                      'display' => 'Redeemed',
                      'read_only' => true,
                      'default' => 0,
                    ],
                  ],
                ],
              ],
            ],
        ]);
    }
}