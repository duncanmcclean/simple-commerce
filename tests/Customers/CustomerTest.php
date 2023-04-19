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

    $this->assertTrue($create instanceof CustomersCustomer);

    $this->assertNotNull($create->id());

    $this->assertSame($create->name(), 'Joe Smith');
    $this->assertSame($create->email(), 'joe.smith@example.com');
    $this->assertSame($create->get('slug'), 'joesmith-at-examplecom');
});

test('can create and ensure customer entry is published', function () {
    $create = Customer::make()
        ->email('joe.smith@example.com')
        ->data([
            'name' => 'Joe Smith',
            'published' => true,
        ]);

    $create->save();

    $this->assertTrue($create instanceof CustomersCustomer);

    $this->assertNotNull($create->id());
    $this->assertSame($create->name(), 'Joe Smith');
    $this->assertSame($create->email(), 'joe.smith@example.com');

    $this->assertTrue($create->get('published'));
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

    $this->assertTrue($findByEmail instanceof CustomersCustomer);

    $this->assertSame($findByEmail->name(), 'Smoke Fire');
    $this->assertSame($findByEmail->email(), 'smoke@fire.com');
    $this->assertSame($findByEmail->get('slug'), 'smoke-at-firecom');
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

    $this->assertTrue($findByEmail instanceof CustomersCustomer);

    $this->assertSame($findByEmail->name(), 'Sam Seaboarn');
    $this->assertSame($findByEmail->email(), 'sam@whitehouse.gov');
    $this->assertSame($findByEmail->get('slug'), 'sam-at-whitehousegov');
});
