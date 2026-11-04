<?php

use DuncanMcClean\SimpleCommerce\Actions\UpdateOrderStatus;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use Illuminate\Support\Carbon;
use Spatie\TestTime\TestTime;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\Stache;
use Statamic\Facades\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->setupCollections();

    $this->action = new UpdateOrderStatus;

    $user = User::make()->id('one')->makeSuper()->save();
    actingAs($user);
});

test('is not visible to products', function () {
    $product = Product::make()
        ->price(1200)
        ->data([
            'title' => 'Medium Jumper',
        ]);

    $product->save();

    $action = $this->action->visibleTo($product->resource());

    expect($action)->toBeFalse();
});

test('can be run by users who can edit orders', function () {
    Role::make('order-editor')->permissions(['access cp', 'view orders entries', 'edit orders entries'])->save();

    $user = tap(User::make()->assignRole('order-editor'))->save();
    $order = tap(Entry::make()->collection('orders')->id(Stache::generateId()))->save();

    expect($this->action->authorize($user, $order))->toBeTrue();
});

test('cant be run by users who can only view orders', function () {
    Role::make('order-viewer')->permissions(['access cp', 'view orders entries'])->save();

    $user = tap(User::make()->assignRole('order-viewer'))->save();
    $order = tap(Entry::make()->collection('orders')->id(Stache::generateId()))->save();

    expect($this->action->authorize($user, $order))->toBeFalse();
});

test('order can have its status updated', function () {
    TestTime::freeze();

    $now = Carbon::now()->timestamp;

    Collection::make('orders')->save();

    $order = Entry::make()
        ->collection('orders')
        ->id(Stache::generateId())
        ->data([
            'order_status' => 'cart',
        ]);

    $order->save();

    $this->action->run([$order], [
        'order_status' => 'dispatched',
    ]);

    $order->fresh();

    expect('dispatched')->toBe($order->data()->get('order_status'));
    expect($order->data()->get('status_log'))->toBe([
        ['status' => 'dispatched', 'timestamp' => $now, 'data' => ['user' => 'one']],
    ]);
});

test('order can have its status updated with reason', function () {
    TestTime::freeze();

    $now = Carbon::now()->timestamp;

    Collection::make('orders')->save();

    $order = Entry::make()
        ->collection('orders')
        ->id(Stache::generateId())
        ->data([
            'order_status' => 'cart',
        ]);

    $order->save();

    $this->action->run([$order], [
        'order_status' => 'dispatched',
        'reason' => 'Handed to the delivery company.',
    ]);

    $order->fresh();

    expect('dispatched')->toBe($order->data()->get('order_status'));
    expect($order->data()->get('status_log'))->toBe([
        ['status' => 'dispatched', 'timestamp' => $now, 'data' => ['user' => 'one', 'reason' => 'Handed to the delivery company.']],
    ]);
});
