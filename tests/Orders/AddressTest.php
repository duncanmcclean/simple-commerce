<?php

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Regions;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

uses(TestCase::class);

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

    $this->assertIsArray($address->toArray());

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

    $this->assertIsArray($address->toArray());

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

    $this->assertIsString((string) $address);

    $this->assertSame((string) $address, 'John Smith,
11 Test Street,
Glasgow,
Scotland,
United Kingdom,
G11 222');
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

    $this->assertIsString((string) $address);

    $this->assertSame((string) $address, 'John Doe,
11 Test Street,
Glasgow,
Scotland,
United Kingdom,
G11 222');
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

    $this->assertIsString($address->name());
    $this->assertSame($address->name(), 'John Smith');
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

    $this->assertIsString($address->firstName());
    $this->assertSame($address->firstName(), 'Joseph');
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

    $this->assertIsString($address->lastName());
    $this->assertSame($address->lastName(), 'Samuel');
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

    $this->assertIsString($address->fullName());
    $this->assertSame($address->fullName(), 'Joseph Samuel');
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

    $this->assertIsString($address->fullName());
    $this->assertSame($address->fullName(), 'Joseph Matthews');
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

    $this->assertIsString($address->addressLine1());
    $this->assertSame($address->addressLine1(), '11 Test Street');
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

    $this->assertIsString($address->addressLine2());
    $this->assertSame($address->addressLine2(), 'Cardonald');
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

    $this->assertIsString($address->city());
    $this->assertSame($address->city(), 'Glasgow');
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

    $this->assertIsArray($address->region());

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

    $this->assertIsArray($address->country());

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

    $this->assertIsString($address->zipCode());
    $this->assertSame($address->zipCode(), 'G11 222');
});
