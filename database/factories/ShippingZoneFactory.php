<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Statamic\Stache\Stache;

$factory->define(ShippingZone::class, function (Faker $faker) {
    return [
        'uuid' => (new Stache())->generateId(),
        'name' => $faker->word,
    ];
});
