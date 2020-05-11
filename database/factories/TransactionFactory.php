<?php

use App\User;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use Faker\Generator as Faker;
use Statamic\Stache\Stache;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\Transaction;

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'uuid' => (new Stache)->generateId(),
        'gateway' => '\DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway',
        'amount' => 15.00,
        'is_complete' => true,
        'is_refunded' => false,
        'gateway_data' => ['id' => 'DummyID'],
        'order_id' => function () {
            return factory(Order::class)->create()->id;
        },
        'customer_id' => function () {
            return factory(User::class)->create()->id;
        },
        'currency_id' => function () {
            return factory(Currency::class)->create()->id;
        },
    ];
});
