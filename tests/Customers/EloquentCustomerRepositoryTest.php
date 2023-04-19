<?php

use DoubleThreeDigital\SimpleCommerce\Customers\CustomerModel;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\UseDatabaseContentDrivers;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(TestCase::class);
uses(RefreshDatabase::class);
uses(UseDatabaseContentDrivers::class);

test('can get all customers', function () {
    CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
    ]);

    CustomerModel::create([
        'name' => 'Sam Seaborn',
        'email' => 'sam@whitehouse.gov',
        'data' => [
            'role' => 'Deputy Communications Director',
        ],
    ]);

    $all = Customer::all();

    expect($all instanceof Collection)->toBeTrue();
    expect(2)->toBe($all->count());
});

test('can find customer', function () {
    $customer = CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
    ]);

    $find = Customer::find($customer->id);

    expect($customer->id)->toBe($find->id());
    expect($customer->name)->toBe($find->name());
    expect($customer->email)->toBe($find->email());
    expect('Press Secretary')->toBe($find->get('role'));
});

test('can find customer with custom column', function () {
    $customer = CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
        'favourite_colour' => 'Orange',
    ]);

    $find = Customer::find($customer->id);

    expect($customer->id)->toBe($find->id());
    expect($customer->name)->toBe($find->name());
    expect($customer->email)->toBe($find->email());
    expect('Press Secretary')->toBe($find->get('role'));
    expect('Orange')->toBe($find->get('favourite_colour'));
});

test('can find customer by email', function () {
    $customer = CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
    ]);

    $find = Customer::findByEmail($customer->email);

    expect($customer->id)->toBe($find->id());
    expect($customer->name)->toBe($find->name());
    expect($customer->email)->toBe($find->email());
    expect('Press Secretary')->toBe($find->get('role'));
});

test('can create', function () {
    $create = Customer::make()
        ->email('sam@whitehouse.gov')
        ->data([
            'name' => 'Sam Seaborne',
        ]);

    $create->save();

    $this->assertNotNull($create->id());
    expect('Sam Seaborne')->toBe($create->name());
    expect('sam@whitehouse.gov')->toBe($create->email());
});

test('can save', function () {
    $customerRecord = CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
    ]);

    $customer = Customer::find($customerRecord->id);

    $customer->set('is_senior_advisor', true);

    $customer->save();

    expect($customerRecord->id)->toBe($customer->id());
    expect(true)->toBe($customer->get('is_senior_advisor'));
});

test('can save with custom column', function () {
    $customerRecord = CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
    ]);

    $customer = Customer::find($customerRecord->id);

    $customer->set('favourite_colour', 'Yellow');

    $customer->save();

    expect($customerRecord->id)->toBe($customer->id());
    expect('Yellow')->toBe($customer->get('favourite_colour'));
});

test('can delete', function () {
    $customerRecord = CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
    ]);

    $customer = Customer::find($customerRecord->id);

    $customer->delete();

    $this->assertDatabaseMissing('customers', [
        'id' => $customerRecord->id,
        'name' => 'CJ Cregg',
        'email' => $customerRecord->email,
    ]);
});
