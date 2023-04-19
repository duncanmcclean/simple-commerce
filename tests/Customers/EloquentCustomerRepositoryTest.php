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

    $this->assertTrue($all instanceof Collection);
    $this->assertSame($all->count(), 2);
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

    $this->assertSame($find->id(), $customer->id);
    $this->assertSame($find->name(), $customer->name);
    $this->assertSame($find->email(), $customer->email);
    $this->assertSame($find->get('role'), 'Press Secretary');
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

    $this->assertSame($find->id(), $customer->id);
    $this->assertSame($find->name(), $customer->name);
    $this->assertSame($find->email(), $customer->email);
    $this->assertSame($find->get('role'), 'Press Secretary');
    $this->assertSame($find->get('favourite_colour'), 'Orange');
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

    $this->assertSame($find->id(), $customer->id);
    $this->assertSame($find->name(), $customer->name);
    $this->assertSame($find->email(), $customer->email);
    $this->assertSame($find->get('role'), 'Press Secretary');
});

test('can create', function () {
    $create = Customer::make()
        ->email('sam@whitehouse.gov')
        ->data([
            'name' => 'Sam Seaborne',
        ]);

    $create->save();

    $this->assertNotNull($create->id());
    $this->assertSame($create->name(), 'Sam Seaborne');
    $this->assertSame($create->email(), 'sam@whitehouse.gov');
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

    $this->assertSame($customer->id(), $customerRecord->id);
    $this->assertSame($customer->get('is_senior_advisor'), true);
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

    $this->assertSame($customer->id(), $customerRecord->id);
    $this->assertSame($customer->get('favourite_colour'), 'Yellow');
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
