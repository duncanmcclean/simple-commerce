<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Statamic\Stache\Stache;

$faker->define(ProductCategory::class, function (Faker $faker) {
    $title = $faker->word;

    return [
        'title' => $title,
        'slug' => str_slug($title),
        'uid' => (new Stache())->generateId(),
    ];
});
