<?php

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use Statamic\Stache\Stache;

$factory->define(CartTax::class, function (Faker $faker) {
    return [
        'uid' => (new Stache())->generateId(),
        'tax_rate_id' => function () {
            return factory(CartTax::class)->create()->id;
        },
        'cart_id' => function() {
            return factory(Cart::class)->create()->id;
        },
    ];
});
