<?php

namespace DoubleThreeDigital\SimpleCommerce;

class Overview
{
    public static function widgets(): array
    {
        return [
            [
                'name' => 'Orders Chart',
                'handle' => 'orders-chart',
                'component' => 'overview-orders-chart',
            ],
            [
                'name' => 'Recent Orders',
                'handle' => 'recent-orders',
                'component' => 'overview-recent-orders',
            ],
            [
                'name' => 'Top Customers',
                'handle' => 'top-customers',
                'component' => 'overview-top-customers',
            ],
            [
                'name' => 'Low Stock Products',
                'handle' => 'low-stock-products',
                'component' => 'overview-low-stock-products',
            ],
        ];
    }
}
