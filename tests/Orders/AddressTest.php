<?php

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Regions;

test('can get address as array', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->toArray())->toBeArray();

    $this->assertSame($address->toArray(), [
        'name' => 'John Smith',
        'first_name' => null,
        'last_name' => null,
        'address_line_1' => '11 Test Street',
        'address_line_2' => null,
        'city' => 'Glasgow',
        'region' => Regions::find('gb-sct'),
        'country' => Countries::find('GB'),
        'zip_code' => 'G11 222',
    ]);
});

test('can get address as array with first name and last name', function () {
    $order = Order::make()
        ->merge([
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->toArray())->toBeArray();

    $this->assertSame($address->toArray(), [
        'name' => null,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address_line_1' => '11 Test Street',
        'address_line_2' => null,
        'city' => 'Glasgow',
        'region' => Regions::find('gb-sct'),
        'country' => Countries::find('GB'),
        'zip_code' => 'G11 222',
    ]);
});

test('can get address as string', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect((string) $address)->toBeString();

    expect('John Smith,
11 Test Street,
Glasgow,
Scotland,
United Kingdom,
G11 222')->toBe((string) $address);
});

test('can get address as string with first name and last name', function () {
    $order = Order::make()
        ->merge([
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect((string) $address)->toBeString();

    expect('John Doe,
11 Test Street,
Glasgow,
Scotland,
United Kingdom,
G11 222')->toBe((string) $address);
});

test('can get name', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_first_name' => null,
            'billing_last_name' => null,
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->name())->toBeString();
    expect('John Smith')->toBe($address->name());
});

test('can get first name', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => null,
            'billing_first_name' => 'Joseph',
            'billing_last_name' => null,
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->firstName())->toBeString();
    expect('Joseph')->toBe($address->firstName());
});

test('can get last name', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => null,
            'billing_first_name' => null,
            'billing_last_name' => 'Samuel',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->lastName())->toBeString();
    expect('Samuel')->toBe($address->lastName());
});

test('can get full name when name is one string', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'Joseph Samuel',
            'billing_first_name' => null,
            'billing_last_name' => null,
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->fullName())->toBeString();
    expect('Joseph Samuel')->toBe($address->fullName());
});

test('can get full name when name is separate first and last names', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => null,
            'billing_first_name' => 'Joseph',
            'billing_last_name' => 'Matthews',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->fullName())->toBeString();
    expect('Joseph Matthews')->toBe($address->fullName());
});

test('can get address line 1', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->addressLine1())->toBeString();
    expect('11 Test Street')->toBe($address->addressLine1());
});

test('can get address line 2', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'first_name' => null,
            'last_name' => null,
            'billing_address' => '11 Test Street',
            'billing_address_line2' => 'Cardonald',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->addressLine2())->toBeString();
    expect('Cardonald')->toBe($address->addressLine2());
});

test('can get city', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->city())->toBeString();
    expect('Glasgow')->toBe($address->city());
});

test('can get region', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->region())->toBeArray();

    $this->assertSame($address->region(), [
        'id' => 'gb-sct',
        'country_iso' => 'GB',
        'name' => 'Scotland',
    ]);
});

test('can get country', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->country())->toBeArray();

    $this->assertSame($address->country(), [
        'iso' => 'GB',
        'name' => 'United Kingdom',
    ]);
});

test('can get zip code', function () {
    $order = Order::make()
        ->merge([
            'billing_name' => 'John Smith',
            'billing_address' => '11 Test Street',
            'billing_city' => 'Glasgow',
            'billing_country' => 'GB',
            'billing_zip_code' => 'G11 222',
            'billing_region' => 'gb-sct',
        ]);

    $address = $order->billingAddress();

    expect($address->zipCode())->toBeString();
    expect('G11 222')->toBe($address->zipCode());
});
