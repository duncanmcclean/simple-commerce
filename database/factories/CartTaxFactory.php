<?php

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use Statamic\Stache\Stache;

$factory->define(CartTax::class, function (Faker $faker) {
    return [
        'uid' => (new Stache())->generateId(),
        'tax_rate_id' => function () {
            return factory(TaxRate::class)->create()->id;
        },
        'cart_id' => function() {
            return factory(Cart::class)->create()->id;
        },
    ];
});
