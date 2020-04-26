<?php

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Statamic\Stache\Stache;

$factory->define(Variant::class, function (Faker $faker) {
    return [
        'uuid'              => (new Stache())->generateId(),
        'name'              => $faker->word,
        'sku'               => $faker->slug,
        'description'       => $faker->text,
        'price'             => 25.99,
        'stock'             => $faker->numberBetween(50, 250),
        'unlimited_stock'   => false,
        'max_quantity'      => 1,
        'product_id'        => function () {
            return factory(Product::class)->create()->id;
        },
        'weight'            => 15.05,
    ];
});
