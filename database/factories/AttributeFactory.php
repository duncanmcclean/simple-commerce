<?php

use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use Faker\Generator as Faker;
use Statamic\Stache\Stache;

$factory->define(Attribute::class, function (Faker $faker) {
    return [
        'uuid'  => (new Stache())->generateId(),
        'key'   => $faker->word,
        'value' => $faker->sentence,
    ];
});
