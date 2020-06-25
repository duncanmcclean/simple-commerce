<?php

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Faker\Generator as Faker;
use Statamic\Stache\Stache;

$factory->define(Product::class, function (Faker $faker) {
    $titles = [
        'Ammazon Alexa',
        'Google Home',
        'Sunglasses',
        'Regular Glasses',
        'Notepad',
        'Phone Case',
        'Wireless Router',
        'Powerline Adapter',
        'Sphero',
        'Keyboard',
        'Mouse',
        'Monitor',
        'MacBook Air',
        'MacBook Pro',
        'iPhone',
        'iPad',
    ];
    $title = $titles[array_rand($titles)];

    return [
        'uuid'                  => (new Stache())->generateId(),
        'title'                 => $title,
        'slug'                  => str_slug($title),
        'is_enabled'            => true,
        'needs_shipping'        => true,
        'tax_rate_id'           => function () {
            return factory(TaxRate::class)->create()->id;
        },
    ];
});
