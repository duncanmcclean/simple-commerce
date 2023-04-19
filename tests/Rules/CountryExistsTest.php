<?php

use DoubleThreeDigital\SimpleCommerce\Rules\CountryExists;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Validator;

uses(TestCase::class);

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
