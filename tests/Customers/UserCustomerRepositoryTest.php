<?php

use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Contracts\Order as ContractsOrder;
use DuncanMcClean\SimpleCommerce\Customers\StacheUserQueryBuilder;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\Invader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Auth\User as StatamicAuthUser;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;
use Statamic\Statamic;

beforeEach(function () {
    Statamic::repository(
        \DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository::class,
        \DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository::class
    );

    File::deleteDirectory(__DIR__.'/../__fixtures__/users');

    app('stache')->stores()->get('users')->clear();
});

afterEach(function () {
    Statamic::repository(
        \DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository::class,
        \DuncanMcClean\SimpleCommerce\Customers\EntryCustomerRepository::class
    );
});

test('can get all customers', function () {
    User::make()->email('james@example.com')->save();
    User::make()->email('ben@example.com')->save();

    $customers = Customer::all();

    expect($customers->count())->toBe(2);
    expect($customers->map->email()->toArray())->toBe([
        'james@example.com',
        'ben@example.com',
    ]);
});

test('can query customers', function () {
    User::make()->email('james@example.com')->set('role', 'Press Secretary')->save();
    User::make()->email('ben@example.com')->save();

    $query = Customer::query();
    expect($query)->toBeInstanceOf(StacheUserQueryBuilder::class);
    expect($query->count())->toBe(2);

    $query = Customer::query()->where('email', 'james@example.com');
    expect($query->count())->toBe(1);
    expect($query->get()[0])->toBeInstanceOf(CustomerContract::class);

    $query = Customer::query()->where('role', 'Press Secretary');
    expect($query->count())->toBe(1);
    expect($query->get()[0])
        ->toBeInstanceOf(CustomerContract::class)
        ->and($query->get()[0]->email())->toBe('james@example.com');
});

test('can find customer by id', function () {
    $user = User::make()->email('james@example.com')->set('name', 'James Example');
    $user->save();

    $find = Customer::find($user->id());

    // $this->assertTrue($find instanceof UserCustomer);

    expect($user->id())->toBe($find->id());
    expect($user->get('name'))->toBe($find->name());
    expect($user->email())->toBe($find->email());
});

test('can findOrFail customer by id', function () {
    $user = User::make()->email('james@example.com')->set('name', 'James Example');
    $user->save();

    $findOrFail = Customer::findOrFail($user->id());

    // $this->assertTrue($find instanceof UserCustomer);

    expect($user->id())->toBe($findOrFail->id());
    expect($user->get('name'))->toBe($findOrFail->name());
    expect($user->email())->toBe($findOrFail->email());

    expect(fn () => Customer::findOrFail('123'))->toThrow(CustomerNotFound::class);
});

test('can find customer by email', function () {
    $user = User::make()->email('ben@example.com')->set('name', 'Ben Example');
    $user->save();

    $findByEmail = Customer::findByEmail($user->email());

    // $this->assertTrue($findByEmail instanceof UserCustomer);

    expect($user->id())->toBe($findByEmail->id());
    expect($user->get('name'))->toBe($findByEmail->name());
    expect($user->email())->toBe($findByEmail->email());
});

test('can create customer', function () {
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

test('can save customer', function () {
    $user = User::make()->id('sarah')->email('sarah@example.com')->set('name', 'Sarah Example');
    $user->save();

    $customer = Customer::find('sarah');
    $customer->name = 'Sarah Test';

    $customer->set('name', 'Sarah Test');

    $customer->save();

    expect('sarah')->toBe($user->id());
    expect('Sarah Test')->toBe($customer->name());
});

test('can delete customer', function () {
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
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toResource = $customer->toResource();

    expect($toResource instanceof UserResource)->toBeTrue();
})->skip();

test('can get customer to augmented array', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');
    $toAugmentedArray = $customer->toAugmentedArray();

    expect($toAugmentedArray)->toBeArray();
})->skip();

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
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('Sam Example <sam@example.com>')->toBe($customer->title());
})->skip();

test('can get slug', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('sam')->toBe($customer->slug());
})->skip();

test('can get site', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect($customer->site())->toBeNull();
})->skip();

test('can get fresh', function () {
    $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
    $user->save();

    $customer = Customer::find('sam');

    expect('Sam Example')->toBe($customer->name());

    $user->set('name', 'Sam Test')->save();

    $fresh = $customer->fresh();

    $this->assertNotSame($customer->name(), 'Sam Example');
    expect('Sam Test')->toBe($customer->name());
})->skip();

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
    $customerInstance = resolve(CustomerContract::class);

    $defaultFieldsInBlueprint = (new Invader($customerInstance))->defaultFieldsInBlueprint();

    expect($defaultFieldsInBlueprint)->toBeArray();
})->skip(true, 'The `defaultFieldsInBlueprint` method doesn\'t seem to exist here.');
