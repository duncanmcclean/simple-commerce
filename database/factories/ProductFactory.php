<?php

use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Statamic\Stache\Stache;

$factory->define(Product::class, function (Faker $faker) {
    $title = $faker->sentence;

    return [
        'uuid'                  => (new Stache())->generateId(),
        'title'                 => $title,
        'slug'                  => str_slug($title),
        'product_category_id'   => function () {
            return factory(ProductCategory::class)->create()->id;
        },
        'is_enabled'            => true,
        'tax_rate_id'           => function () {
            return factory(TaxRate::class)->create()->id;
        },
        'needs_shipping'        => true,
    ];
});
