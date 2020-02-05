<?php

use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Statamic\Stache\Stache;

$factory->define(Product::class, function (Faker $faker) {
    $title = $faker->sentence;

    return [
        'title' => $title,
        'slug' => str_slug($title),
        'product_category_id' => function () {
            return factory(ProductCategory::class)->create()->id;
        },
        'uuid' => (new Stache())->generateId(),
        'is_enabled' => true,
        'description' => $faker->text,
    ];
});
