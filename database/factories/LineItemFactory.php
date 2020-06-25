<?php

use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Faker\Generator as Faker;
use Statamic\Stache\Stache;

$factory->define(LineItem::class, function (Faker $faker) {
    $variant = factory(Variant::class)->create();

    return [
        'uuid'              => (new Stache())->generateId(),
        'sku'               => $variant->sku,
        'description'       => $variant->title,
        'note'              => '',
        'price'             => $variant->price,
        'total'             => $variant->price,
        'weight'            => $faker->randomFloat(),
        'quantity'          => 1,
        'order_id'          => function () {
            return factory(Order::class)->create()->id;
        },
        'variant_id'        => function () {
            return factory(Variant::class)->create()->id;
        },
        'tax_rate_id'       => function () {
            return factory(TaxRate::class)->create()->id;
        },
        'shipping_rate_id'  => function () {
            return factory(ShippingRate::class)->create()->id;
        },
        'coupon_id'         => function () {
            return factory(Coupon::class)->create()->id;
        },
    ];
});
