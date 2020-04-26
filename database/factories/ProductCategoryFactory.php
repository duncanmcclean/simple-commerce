<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Statamic\Stache\Stache;

$factory->define(ProductCategory::class, function (Faker $faker) {
    return [
        'uuid'  => (new Stache())->generateId(),
        'title' => $faker->word,
        'slug'  => $faker->slug,
    ];
});
