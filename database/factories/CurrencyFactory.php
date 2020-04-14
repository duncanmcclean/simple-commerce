<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use Statamic\Stache\Stache;

$factory->define(Currency::class, function (Faker $faker) {
    return [
        'iso'       => 'USD',
        'uuid'      => (new Stache())->generateId(),
        'symbol'    => '$',
        'name'      => 'Unites States Dollar',
    ];
});
