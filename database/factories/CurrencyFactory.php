<?php

use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use Statamic\Stache\Stache;

$factory->define(Currency::class, function (Faker $faker) {
    return [
        'uuid'      => (new Stache())->generateId(),
        'name'      => 'Unites States Dollar',
        'iso'       => 'USD',
        'symbol'    => '$',
    ];
});
