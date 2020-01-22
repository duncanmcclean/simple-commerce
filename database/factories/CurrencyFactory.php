<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use Statamic\Stache\Stache;

$faker->define(Currency::class, function (Faker $faker) {
    return [
        'iso' => 'USD',
        'primary' => true,
        'uid' => (new Stache())->generateId(),
        'symbol' => '£',
        'name' => 'Unites States Dollar',
    ];
});
