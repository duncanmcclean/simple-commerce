<?php

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use Statamic\Stache\Stache;

$factory->define(CartItem::class, function (Faker $faker) {
    return [
        'uuid' => (new Stache())->generateId(),
        'product_id' => function() {
            return factory(Product::class)->create()->id;
        },
        'variant_id' => function() {
            return factory(Variant::class)->create()->id;
        },
        'quantity' => 1,
        'cart_id' => function() {
            return factory(Cart::class)->create()->id;
        },
    ];
});
