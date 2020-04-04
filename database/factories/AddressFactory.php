<?php

use App\User;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use Statamic\Stache\Stache;

$factory->define(Address::class, function (Faker $faker) {
    return [
        'country_id' => Country::where('iso', $faker->countryCode)->first(),
        'state_id' => State::where('abreviation', $faker->stateAbbr)->first(),
        'name' => $faker->name,
        'address1' => $faker->streetAddress,
        'address2' => null,
        'address3' => null,
        'city' => $faker->city,
        'zip_code' => $faker->postcode,
        'customer_id' => function() {
            return factory(User::class)->create()->id;
        },
        'uuid' => (new Stache())->generateId(),
    ];
});
