<?php

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as CustomerContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as ContractsOrder;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Auth\User as StatamicAuthUser;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;
use Statamic\Statamic;

uses(TestCase::class);
beforeEach(function () {
    Statamic::repository(
        \DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository::class,
        \DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository::class
    );

    File::deleteDirectory(__DIR__.'/../__fixtures__/users');

    app('stache')->stores()->get('users')->clear();
});

afterEach(function () {
    Statamic::repository(
        \DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository::class,
        \DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class
    );
});


test('can get all users', function () {
    User::make()
        ->email('james@example.com')
        ->save();

    User::make()
        ->email('ben@example.com')
        ->save();

    $all = Customer::all();

    $this->assertTrue($all instanceof Collection);
    $this->assertSame($all->count(), 2);
});

test('can query users', function () {
    User::make()
        ->email('james@example.com')
        ->save();

    User::make()
        ->email('ben@example.com')
        ->save();

    $query = Customer::all();

    $this->assertTrue($query instanceof Collection);
    $this->assertSame($query->count(), 2);
});

test('can find user', function () {
    $user = User::make()->email('james@example.com')->set('name', 'James Example');
    $user->save();

    $find = Customer::find($user->id());

    // $this->assertTrue($find instanceof UserCustomer);

    $this->assertSame($find->id(), $user->id());
    $this->assertSame($find->name(), $user->get('name'));
    $this->assertSame($find->email(), $user->email());
});

test('can find user by email', function () {
    $user = User::make()->email('ben@example.com')->set('name', 'Ben Example');
    $user->save();

    $findByEmail = Customer::findByEmail($user->email());

    // $this->assertTrue($findByEmail instanceof UserCustomer);

    $this->assertSame($findByEmail->id(), $user->id());
    $this->assertSame($findByEmail->name(), $user->get('name'));
    $this->assertSame($findByEmail->email(), $user->email());
});

test('can create', function () {
    $create = Customer::make()
        ->email('joe.smith@example.com')
        ->data([
            'name' => 'Joe Smith',
        ]);

    $create->save();

    // $this->assertTrue($create instanceof UserCustomer);

    $this->assertNotNull($create->id());
    $this->assertSame($create->name(), 'Joe Smith');
    $this->assertSame($create->email(), 'joe.smith@example.com');
});

test('can save', function () {
    $user = User::make()->id('sarah')->email('sarah@example.com')->set('name', 'Sarah Example');
    $user->save();

    $customer = Customer::find('sarah');
    $customer->name = 'Sarah Test';

    $customer->set('name', 'Sarah Test');

    $customer->save();

    $this->assertSame($user->id(), 'sarah');
    $this->assertSame($customer->name(), 'Sarah Test');
});

test('can delete', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $customer->delete();

    $this->assertNull(User::find('sam'));
});

test('can get user', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertTrue($customer->resource() instanceof StatamicAuthUser);
});

test('can get customer to resource', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toResource = $customer->toResource();

    $this->assertTrue($toResource instanceof UserResource);
});

test('can get customer to augmented array', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toAugmentedArray = $customer->toAugmentedArray();

    $this->assertIsArray($toAugmentedArray);
});

test('can get customer to array', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toArray = $customer->toArray();

    $this->assertIsArray($toArray);
});

test('can get id', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertSame($customer->id(), 'sam');
});

test('can get title', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertSame($customer->title(), 'Sam Example <sam@example.com>');
});

test('can get slug', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertSame($customer->slug(), 'sam');
});

test('can get site', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertNull($customer->site());
});

test('can get fresh', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertSame($customer->name(), 'Sam Example');

    $user->set('name', 'Sam Test')->save();

    $fresh = $customer->fresh();

    $this->assertNotSame($customer->name(), 'Sam Example');
    $this->assertSame($customer->name(), 'Sam Test');
});

test('can get name', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertSame($customer->name(), 'Sam Example');
});

test('can get email', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertSame($customer->email(), 'sam@example.com');
});

test('can get orders', function () {
    $order = Order::make()->merge(['foo' => 'bar']);
    $order->save();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example')->set('orders', [$order->id()]);
    $user->save();

    $customer = Customer::find('sam');

    $this->assertTrue($customer->orders() instanceof Collection);
    $this->assertTrue($customer->orders()->first() instanceof ContractsOrder);

    $this->assertSame($customer->orders()->first()->get('foo'), 'bar');
});

test('can get mail notification route', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $this->assertSame($customer->routeNotificationForMail(), 'sam@example.com');
});

test('can get blueprint default fields', function () {
    $this->markTestSkipped("The `defaultFieldsInBlueprint` method doesn't seem to exist here.");

    $customerInstance = resolve(CustomerContract::class);

    $defaultFieldsInBlueprint = (new Invader($customerInstance))->defaultFieldsInBlueprint();

    $this->assertIsArray($defaultFieldsInBlueprint);
});
