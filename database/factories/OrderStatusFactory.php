<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\Stache\Stache;

$faker->define(OrderStatus::class, function (Faker $faker) {
    $name = $faker->word;

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'description' => '',
        'color' => 'green' ?? 'blue' ?? 'orange',
        'uid' => (new Stache())->generateId(),
    ];
});
