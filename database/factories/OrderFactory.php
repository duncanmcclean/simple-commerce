<?php

use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Statamic\Stache\Stache;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'payment_intent' => null,
        'billing_address_id' => function () {
            return factory(Address::class)->create()->id;
        },
        'address_address_id' => function () {
            return factory(Address::class)->create()->id;
        },
        'customer_id' => function () {
            return factory(Customer::class)->create()->id;
        },
        'order_status_id' => function () {
            return factory(OrderStatus::class)->create()->id;
        },
        'items' => null,
        'total' => 00.00,
        'currency_id' => function () {
            return factory(Currency::class)->create()->id;
        },
        'uuid' => (new Stache())->generateId(),
    ];
});
