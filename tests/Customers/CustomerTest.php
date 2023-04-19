<?php

use DoubleThreeDigital\SimpleCommerce\Customers\Customer as CustomersCustomer;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

uses(TestCase::class);

test('can create', function () {
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

test('can create and ensure customer entry is published', function () {
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

test('can find by id', function () {
    Entry::make()
        ->collection('customers')
        ->id($id = Stache::generateId())
        ->slug('smoke-at-firecom')
        ->data([
            'name' => 'Smoke Fire',
            'email' => 'smoke@fire.com',
        ])
        ->save();

    $findByEmail = Customer::find($id);

    expect($findByEmail instanceof CustomersCustomer)->toBeTrue();

    expect('Smoke Fire')->toBe($findByEmail->name());
    expect('smoke@fire.com')->toBe($findByEmail->email());
    expect('smoke-at-firecom')->toBe($findByEmail->get('slug'));
});

test('can find by email', function () {
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
