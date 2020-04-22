<?php

use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use Statamic\Stache\Stache;

$factory->define(Country::class, function (Faker $faker) {
    return [
        'name'              => $faker->country,
        'iso'               => $faker->countryISOAlpha3,
        'uuid'              => (new Stache)->generateId(),
        'shipping_zone_id'  => function () {
            return factory(ShippingZone::class)->create()->id;
        },
    ];
});
