<?php

use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use Statamic\Stache\Stache;

$factory->define(Country::class, function (Faker $faker) {
    return [
        'uuid'              => (new Stache)->generateId(),
        'name'              => $faker->country,
        'iso'               => $faker->countryISOAlpha3,
        'shipping_zone_id'  => function () {
            return factory(ShippingRate::class)->create()->id;
        },
    ];
});
