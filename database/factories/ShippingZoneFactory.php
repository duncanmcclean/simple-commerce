<?php

use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Faker\Generator as Faker;
use Statamic\Stache\Stache;

$factory->define(ShippingZone::class, function (Faker $faker) {
    return [
        'uuid' => (new Stache())->generateId(),
        'name' => $faker->word,
    ];
});
