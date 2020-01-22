<?php

use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Statamic\Stache\Stache;
use function GuzzleHttp\Psr7\str;

$factory->define(Product::class, function (Faker $faker) {
    $title = $faker->title;

    return [
        'title' => $title,
        'slug' => str_slug($title),
        'product_category_id' => function () {
            return factory(ProductCategory::class)->create()->id;
        },
        'uid' => (new Stache())->generateId(),
        'is_enabled' => true,
        'description' => null,
    ];
});
