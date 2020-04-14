<?php

use App\User;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Config;

$factory->define(Order::class, function (Faker $faker) {
    $currency = factory(Currency::class)->create();
    Config::set('simple-commerce.currency.iso', $currency->iso);

    return [
        'uuid'                  => (new Stache())->generateId(),
        'billing_address_id'    => function () {
            return factory(Address::class)->create()->id;
        },
        'shipping_address_id'   => function () {
            return factory(Address::class)->create()->id;
        },
        'gateway'               => '\DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway',
        'customer_id'           => function () {
            return factory(User::class)->create()->id;
        },
        'order_status_id'       => function () {
            return factory(OrderStatus::class)->create()->id;
        },
        'item_total'            => 00.00,
        'tax_total'             => 00.00,
        'shipping_total'        => 00.00,
        'total'                 => 00.00,
        'is_paid'               => false,
        'is_completed'          => false,
        'currency_id'           => function () {
            return factory(Currency::class)->create()->id;
        },
    ];
});
