<?php

use DuncanMcClean\SimpleCommerce\Rules\CountryExists;
use Illuminate\Support\Facades\Validator;

it('passes for matching iso code', function () {
    $data = [
        'country' => 'GB',
    ];

    $validate = Validator::make($data, [
        'country' => [new CountryExists()],
    ]);

    expect($validate->fails())->toBeFalse();
});

it('fails for made up country', function () {
    $data = [
        'country' => 'stataland',
    ];

    $validate = Validator::make($data, [
        'country' => [new CountryExists()],
    ]);

    expect($validate->fails())->toBeTrue();
});
