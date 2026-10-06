<?php

use DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository;
use DuncanMcClean\SimpleCommerce\Customers\EntryCustomerRepository;
use DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository;
use DuncanMcClean\SimpleCommerce\Listeners\EnforceUserBlueprintFields;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Statamic\Events\UserBlueprintFound;
use Statamic\Facades\Blueprint;
use Statamic\Statamic;

afterEach(function () {
    Blueprint::find('user')?->delete();
});

test('fields can be added to user blueprint', function () {
    Config::set('simple-commerce.content.customers', [
        'repository' => UserCustomerRepository::class,
    ]);

    Statamic::repository(
        CustomerRepository::class,
        UserCustomerRepository::class
    );

    File::deleteDirectory(__DIR__.'/../__fixtures__/users');

    app('stache')->stores()->get('users')->clear();

    $blueprint = Blueprint::make('user')->save();

    $event = new UserBlueprintFound($blueprint);

    $handle = (new EnforceUserBlueprintFields)->handle($event);

    $this->assertTrue($handle->hasField('orders'));

    Statamic::repository(
        CustomerRepository::class,
        EntryCustomerRepository::class
    );
});

test('fields can not be added to user blueprint when customer driver is not user', function () {
    $blueprint = Blueprint::make('user')->save();

    $event = new UserBlueprintFound($blueprint);

    $handle = (new EnforceUserBlueprintFields)->handle($event);

    $this->assertFalse($handle->hasField('orders'));
});
