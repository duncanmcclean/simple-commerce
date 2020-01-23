<?php

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Statamic\Stache\Stache;

$factory->define(Variant::class, function (Faker $faker) {
    return [
        'sku' => $faker->slug,
        'price' => 10.55,
        'stock' => $faker->numberBetween(50, 250),
        'unlimited_stock' => false,
        'max_quantity' => 1,
        'product_id' => function () {
            return factory(Product::class)->create()->id;
        },
        'uid' => (new Stache())->generateId(),
        'description' => $faker->text,
        'variant_attributes' => [
            [
                '_id' => 'row_1',
                'key' => 'color',
                'value' => 'Red',
            ],
        ],
        'name' => $faker->word,
    ];
});
