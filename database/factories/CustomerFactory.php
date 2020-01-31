<?php

use DoubleThreeDigital\SimpleCommerce\Models\Address;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use Statamic\Stache\Stache;

$factory->define(Customer::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'uid' => (new Stache())->generateId(),
    ];
});
