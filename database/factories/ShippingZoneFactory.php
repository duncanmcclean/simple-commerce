<?php

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Statamic\Stache\Stache;

$factory->define(ShippingZone::class, function (Faker $faker) {
    return [
        'uid' => (new Stache())->generateId(),
        'country_id' => function () {
            return factory(Country::class)->create()->id;
        },
        'state_id' => function () {
            return factory(State::class)->create()->id;
        },
        'start_of_zip_code' => 'G72',
        'rate' => $faker->randomElement([5, 10, 15, 20, 25, 30, 35, 40, 50, 60, 70, 75, 80, 85, 90, 95, 100]),
    ];
});
