<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Statamic\Stache\Stache;

$factory->define(ShippingRate::class, function (Faker $faker) {
    return [
        'uuid' => (new Stache())->generateId(),
        'name' => $faker->word,
        'type' => 'price-based',
        'minimum' => 00.00,
        'maximum' => 15.00,
        'rate' => 2.50,
        'shipping_zone_id' => function () {
            return factory(ShippingZone::class)->create();
        },
    ];
});
