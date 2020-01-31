<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use Statamic\Stache\Stache;

$factory->define(Cart::class, function (Faker $faker) {
    return [
        'uid' => (new Stache())->generateId(),
    ];
});
