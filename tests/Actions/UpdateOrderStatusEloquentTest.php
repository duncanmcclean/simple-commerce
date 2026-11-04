<?php

use DuncanMcClean\SimpleCommerce\Actions\UpdateOrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\OrderModel;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\UseDatabaseContentDrivers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\Role;
use Statamic\Facades\User;

uses(RefreshDatabase::class);
uses(UseDatabaseContentDrivers::class);

beforeEach(function () {
    $this->action = new UpdateOrderStatus;
});

test('can be run by users who can edit orders', function () {
    Role::make('order-editor')->permissions(['access cp', 'view orders', 'edit orders'])->save();

    $user = tap(User::make()->assignRole('order-editor'))->save();
    $order = OrderModel::create();

    expect($this->action->authorize($user, $order))->toBeTrue();
});

test('cant be run by users who can only view orders', function () {
    Role::make('order-viewer')->permissions(['access cp', 'view orders'])->save();

    $user = tap(User::make()->assignRole('order-viewer'))->save();
    $order = OrderModel::create();

    expect($this->action->authorize($user, $order))->toBeFalse();
});
