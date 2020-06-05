<?php

use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use Faker\Generator as Faker;
use Statamic\Stache\Stache;

$factory->define(Coupon::class, function (Faker $faker) {
    $types = [
        'percent_discount',
        'fixed_discount',
        'free_shipping',
    ];
    $type = $types[array_rand($types)];

    return [
        'uuid'          => (new Stache())->generateId(),
        'name'          => '10% Off Cart',
        'code'          => '10OFFCART',
        'type'          => $type,
        'value'         => 10,
        'minimum_total' => 00.00,
        'total_uses'    => 0,
        'start_date'    => now(),
        'end_date'      => now()->addWeeks(2),
    ];
});
