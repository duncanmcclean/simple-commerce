<?php

use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Customers\CustomerModel;
use DuncanMcClean\SimpleCommerce\Customers\EloquentQueryBuilder;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\UseDatabaseContentDrivers;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(UseDatabaseContentDrivers::class);

it('can get all customers', function () {
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

    $customers = Customer::all();

    expect($customers->count())->toBe(2);
    expect($customers->map->email()->toArray())->toBe([
        'cj@whitehouse.gov',
        'sam@whitehouse.gov',
    ]);
});

it('can query customers', function () {
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

    $query = Customer::query();
    expect($query)->toBeInstanceOf(EloquentQueryBuilder::class);
    expect($query->count())->toBe(2);

    $query = Customer::query()->where('email', 'cj@whitehouse.gov');
    expect($query->count())->toBe(1);
    expect($query->get()[0])->toBeInstanceOf(CustomerContract::class);

    $query = Customer::query()->where('role', 'Press Secretary');
    expect($query->count())->toBe(1);
    expect($query->get()[0])
        ->toBeInstanceOf(CustomerContract::class)
        ->and($query->get()[0]->email())->toBe('cj@whitehouse.gov');
});

it('can find customer by id', function () {
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

it('can findOrFail customer by id', function () {
    $customer = CustomerModel::create([
        'name' => 'CJ Cregg',
        'email' => 'cj@whitehouse.gov',
        'data' => [
            'role' => 'Press Secretary',
        ],
    ]);

    $findOrFail = Customer::findOrFail($customer->id);

    expect($customer->id)->toBe($findOrFail->id());
    expect($customer->name)->toBe($findOrFail->name());
    expect($customer->email)->toBe($findOrFail->email());
    expect('Press Secretary')->toBe($findOrFail->get('role'));

    expect(fn () => Customer::findOrFail(123))->toThrow(CustomerNotFound::class);
});

it('can find customer by email', function () {
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

it('can find customer with custom column', function () {
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

it('can create customer', function () {
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

it('can save customer', function () {
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

it('can save customer with custom column', function () {
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

it('can delete customer', function () {
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
