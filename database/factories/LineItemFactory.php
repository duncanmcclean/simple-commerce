<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use Statamic\Stache\Stache;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;

$factory->define(LineItem::class, function (Faker $faker) {
    $variant = factory(Variant::class)->create();

    return [
        'uuid'                  => (new Stache())->generateId(),
        'order_id'              => function () {
            return factory(Order::class)->create()->id;
        },
        'variant_id'            => function ($variant) {
            return $variant->id;
        },
        'tax_category_id'       => function () {
            // TODO: link the factory
        },
        'shipping_category_id'  => function () {
            // TODO: link the factory
        },
        'description'           => $variant->name,
        'sku'                   => $variant->sku,
        'price'                 => $variant->price,
        'weight'                => 00.00,
        'height'                => 00.00,
        'length'                => 00.00,
        'width'                 => 00.00,
        'total'                 => $variant->price,
        'quantity'              => 1,
        'note'                  => null,
    ];
});
