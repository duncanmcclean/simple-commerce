<?php

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as CustomerContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as ContractsOrder;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Auth\User as StatamicAuthUser;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;
use Statamic\Statamic;

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

    expect($all instanceof Collection)->toBeTrue();
    expect(2)->toBe($all->count());
});

test('can query users', function () {
    User::make()
        ->email('james@example.com')
        ->save();

    User::make()
        ->email('ben@example.com')
        ->save();

    $query = Customer::all();

    expect($query instanceof Collection)->toBeTrue();
    expect(2)->toBe($query->count());
});

test('can find user', function () {
    $user = User::make()->email('james@example.com')->set('name', 'James Example');
    $user->save();

    $find = Customer::find($user->id());

    // $this->assertTrue($find instanceof UserCustomer);

    expect($user->id())->toBe($find->id());
    expect($user->get('name'))->toBe($find->name());
    expect($user->email())->toBe($find->email());
});

test('can find user by email', function () {
    $user = User::make()->email('ben@example.com')->set('name', 'Ben Example');
    $user->save();

    $findByEmail = Customer::findByEmail($user->email());

    // $this->assertTrue($findByEmail instanceof UserCustomer);

    expect($user->id())->toBe($findByEmail->id());
    expect($user->get('name'))->toBe($findByEmail->name());
    expect($user->email())->toBe($findByEmail->email());
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
    expect('Joe Smith')->toBe($create->name());
    expect('joe.smith@example.com')->toBe($create->email());
});

test('can save', function () {
    $user = User::make()->id('sarah')->email('sarah@example.com')->set('name', 'Sarah Example');
    $user->save();

    $customer = Customer::find('sarah');
    $customer->name = 'Sarah Test';

    $customer->set('name', 'Sarah Test');

    $customer->save();

    expect('sarah')->toBe($user->id());
    expect('Sarah Test')->toBe($customer->name());
});

test('can delete', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    $customer->delete();

    expect(User::find('sam'))->toBeNull();
});

test('can get user', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect($customer->resource() instanceof StatamicAuthUser)->toBeTrue();
});

test('can get customer to resource', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toResource = $customer->toResource();

    expect($toResource instanceof UserResource)->toBeTrue();
});

test('can get customer to augmented array', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toAugmentedArray = $customer->toAugmentedArray();

    expect($toAugmentedArray)->toBeArray();
});

test('can get customer to array', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toArray = $customer->toArray();

    expect($toArray)->toBeArray();
});

test('can get id', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('sam')->toBe($customer->id());
});

test('can get title', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('Sam Example <sam@example.com>')->toBe($customer->title());
});

test('can get slug', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('sam')->toBe($customer->slug());
});

test('can get site', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect($customer->site())->toBeNull();
});

test('can get fresh', function () {
    $this->markTestSkipped();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('Sam Example')->toBe($customer->name());

    $user->set('name', 'Sam Test')->save();

    $fresh = $customer->fresh();

    $this->assertNotSame($customer->name(), 'Sam Example');
    expect('Sam Test')->toBe($customer->name());
});

test('can get name', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('Sam Example')->toBe($customer->name());
});

test('can get email', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('sam@example.com')->toBe($customer->email());
});

test('can get orders', function () {
    $order = Order::make()->merge(['foo' => 'bar']);
    $order->save();

    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example')->set('orders', [$order->id()]);
    $user->save();

    $customer = Customer::find('sam');

    expect($customer->orders() instanceof Collection)->toBeTrue();
    expect($customer->orders()->first() instanceof ContractsOrder)->toBeTrue();

    expect('bar')->toBe($customer->orders()->first()->get('foo'));
});

test('can get mail notification route', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('sam@example.com')->toBe($customer->routeNotificationForMail());
});

test('can get blueprint default fields', function () {
    $this->markTestSkipped("The `defaultFieldsInBlueprint` method doesn't seem to exist here.");

    $customerInstance = resolve(CustomerContract::class);

    $defaultFieldsInBlueprint = (new Invader($customerInstance))->defaultFieldsInBlueprint();

    expect($defaultFieldsInBlueprint)->toBeArray();
});
