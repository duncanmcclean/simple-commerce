<?php

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Faker\Generator as Faker;
use Statamic\Stache\Stache;

$factory->define(TaxRate::class, function (Faker $faker) {
    return [
        'uuid'              => (new Stache())->generateId(),
        'name'              => $faker->word,
        'description'       => $faker->realText(),
        'rate'              => array_rand([5, 10, 15, 20, 25, 30, 35, 40, 50, 60, 70, 75, 80, 85, 90, 95, 100]),
    ];
});
