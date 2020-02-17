<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use Statamic\Stache\Stache;

$factory->define(Attribute::class, function (Faker $faker) {
    return [
        'uuid' => (new Stache())->generateId(),
        'key' => $faker->word,
        'value' => $faker->sentence,
    ];
});
