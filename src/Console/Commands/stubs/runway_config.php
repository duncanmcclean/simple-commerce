<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Configure the resources (models) you'd like to be available in Runway.
    |
    */

    'resources' => [
        \DoubleThreeDigital\SimpleCommerce\Customers\CustomerModel::class => [
            'name' => 'Customers',
            'handle' => 'customers',
            'blueprint' => 'customers',
        ],

        \DoubleThreeDigital\SimpleCommerce\Orders\OrderModel::class => [
            'name' => 'Orders',
            'handle' => 'orders',
            'blueprint' => 'order',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Disable Migrations?
    |--------------------------------------------------------------------------
    |
    | Should Runway's migrations be disabled?
    | (eg. not automatically run when you next vendor:publish)
    |
    */

    'disable_migrations' => false,

];
