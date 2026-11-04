<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Statamic\Facades\Role;
use Statamic\Facades\User;

use function Pest\Laravel\actingAs;

uses(SetupCollections::class);

it('can get the status log as a user who can view orders', function () {
    Role::make('order-viewer')->permissions(['access cp', 'view orders entries'])->save();

    $order = Order::make()->save();
    $order->updateOrderStatus(OrderStatus::Placed)->save();

    actingAs(tap(User::make()->assignRole('order-viewer'))->save())
        ->post('/cp/simple-commerce/fieldtype-api/status-log', [
            'order_id' => $order->id,
        ])
        ->assertOk()
        ->assertJsonPath('0.events.0.status', 'placed');
});

it('cant get the status log as a user who cant view orders', function () {
    Role::make('cp-user')->permissions(['access cp'])->save();

    $order = Order::make()->save();
    $order->updateOrderStatus(OrderStatus::Placed)->save();

    actingAs(tap(User::make()->assignRole('cp-user'))->save())
        ->post('/cp/simple-commerce/fieldtype-api/status-log', [
            'order_id' => $order->id,
        ])
        ->assertForbidden();
});
