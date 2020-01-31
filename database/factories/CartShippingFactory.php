<?php

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use Statamic\Stache\Stache;

$factory->define(CartShipping::class, function (Faker $faker) {
    return [
        'uid' => (new Stache())->generateId(),
        'shipping_zone_id' => function () {
            return factory(ShippingZone::class)->create()->id;
        },
        'cart_id' => function () {
            return factory(Cart::class)->create()->id;
        },
    ];
});
