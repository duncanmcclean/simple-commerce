<?php

use App\User;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use Statamic\Stache\Stache;

$factory->define(Address::class, function (Faker $faker) {
    return [
        'uuid'          => (new Stache())->generateId(),
        'name'          => $faker->name,
        'address1'      => $faker->streetAddress,
        'address2'      => null,
        'address3'      => null,
        'city'          => $faker->city,
        'zip_code'      => $faker->zip_code,
        'state_id'      => function () {
            return factory(State::class)->create()->id;
        },
        'country_id'    => function () {
            return factory(Country::class)->create()->id;
        },
        'customer_id'   => function () {
            return factory(User::class)->create()->id;
        },
    ];  
});
