<?php

use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Customers\Customer as CustomersCustomer;
use DuncanMcClean\SimpleCommerce\Customers\EntryQueryBuilder;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

uses(PreventsSavingStacheItemsToDisk::class);

afterEach(function () {
    Collection::find('customers')->queryEntries()->get()->each->delete();
});

it('can get all customers', function () {
    Customer::make()->email('cj.cregg@whitehouse.gov')->save();
    Customer::make()->email('leo.mcgary@whitehouse.gov')->save();
    Customer::make()->email('sam.seaborne@whitehouse.gov')->save();

    $customers = Customer::all();

    expect($customers->count())->toBe(3);
    expect($customers->map->email()->toArray())->toBe([
        'cj.cregg@whitehouse.gov',
        'leo.mcgary@whitehouse.gov',
        'sam.seaborne@whitehouse.gov',
    ]);
});

it('can query customers', function () {
    Customer::make()->email('cj.cregg@whitehouse.gov')->set('role', 'Press Secretary')->save();
    Customer::make()->email('leo.mcgary@whitehouse.gov')->save();
    Customer::make()->email('sam.seaborne@whitehouse.gov')->save();

    $query = Customer::query();
    expect($query)->toBeInstanceOf(EntryQueryBuilder::class);
    expect($query->count())->toBe(3);

    $query = Customer::query()->where('email', 'cj.cregg@whitehouse.gov');
    expect($query->count())->toBe(1);
    expect($query->get()[0])->toBeInstanceOf(CustomerContract::class);

    $query = Customer::query()->where('role', 'Press Secretary');
    expect($query->count())->toBe(1);
    expect($query->get()[0])
        ->toBeInstanceOf(CustomerContract::class)
        ->and($query->get()[0]->email())->toBe('cj.cregg@whitehouse.gov');
});

it('can find customer by id', function () {
    Entry::make()
        ->collection('customers')
        ->id($id = Stache::generateId())
        ->slug('smoke-at-firecom')
        ->data([
            'name' => 'Smoke Fire',
            'email' => 'smoke@fire.com',
        ])
        ->save();

    $find = Customer::find($id);

    expect($find instanceof CustomersCustomer)->toBeTrue();

    expect('Smoke Fire')->toBe($find->name());
    expect('smoke@fire.com')->toBe($find->email());
    expect('smoke-at-firecom')->toBe($find->get('slug'));
});

it('can findOrFail customer by id', function () {
    Entry::make()
        ->collection('customers')
        ->id($id = Stache::generateId())
        ->slug('smoke-at-firecom')
        ->data([
            'name' => 'Smoke Fire',
            'email' => 'smoke@fire.com',
        ])
        ->save();

    $find = Customer::findOrFail($id);

    expect($find instanceof CustomersCustomer)->toBeTrue();

    expect('Smoke Fire')->toBe($find->name());
    expect('smoke@fire.com')->toBe($find->email());
    expect('smoke-at-firecom')->toBe($find->get('slug'));

    expect(fn () => Customer::findOrFail(123))->toThrow(CustomerNotFound::class);
});

it('can find customer by email', function () {
    Entry::make()
        ->collection('customers')
        ->id(Stache::generateId())
        ->slug('sam-at-whitehousegov')
        ->data([
            'name' => 'Sam Seaboarn',
            'email' => 'sam@whitehouse.gov',
        ])
        ->save();

    $findByEmail = Customer::findByEmail('sam@whitehouse.gov');

    expect($findByEmail instanceof CustomersCustomer)->toBeTrue();

    expect('Sam Seaboarn')->toBe($findByEmail->name());
    expect('sam@whitehouse.gov')->toBe($findByEmail->email());
    expect('sam-at-whitehousegov')->toBe($findByEmail->get('slug'));
});

it('can make customer', function () {
    $customer = Customer::make();

    expect($customer)->toBeInstanceOf(CustomerContract::class);
});

it('can save customer', function () {
    $create = Customer::make()
        ->email('joe.smith@example.com')
        ->data([
            'name' => 'Joe Smith',
        ]);

    $create->save();

    expect($create instanceof CustomersCustomer)->toBeTrue();

    $this->assertNotNull($create->id());

    expect('Joe Smith')->toBe($create->name());
    expect('joe.smith@example.com')->toBe($create->email());
    expect('joesmith-at-examplecom')->toBe($create->get('slug'));
});

it('can save customer and ensure entry is published', function () {
    $create = Customer::make()
        ->email('joe.smith@example.com')
        ->data([
            'name' => 'Joe Smith',
            'published' => true,
        ]);

    $create->save();

    expect($create instanceof CustomersCustomer)->toBeTrue();

    $this->assertNotNull($create->id());
    expect('Joe Smith')->toBe($create->name());
    expect('joe.smith@example.com')->toBe($create->email());

    expect($create->get('published'))->toBeTrue();
});

it('can delete customer', function () {
    $customer = Customer::make()
        ->email('joe.smith@example.com')
        ->data([
            'name' => 'Joe Smith',
        ])
        ->save();

    expect($customer->resource())->toBeInstanceOf(\Statamic\Contracts\Entries\Entry::class);

    $customer->delete();

    expect($customer->resource()->fresh())->toBeNull();
});
