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
        \DuncanMcClean\SimpleCommerce\Customers\CustomerModel::class => [
            'name' => 'Customers',
            'handle' => 'customers',
            'hidden' => true,
        ],

        \DuncanMcClean\SimpleCommerce\Orders\OrderModel::class => [
            'name' => 'Orders',
            'handle' => 'orders',
            'hidden' => true,
            'read_only' => true,
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
