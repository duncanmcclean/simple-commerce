<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\Stache\Stache;

$factory->define(OrderStatus::class, function (Faker $faker) {
    $name = $faker->word;

    return [
        'uuid' => (new Stache())->generateId(),
        'name'          => $name,
        'slug'          => str_slug($name),
        'description'   => '',
        'color'         => 'green' ?? 'blue' ?? 'orange',
        'primary'       => false,
    ];
});
