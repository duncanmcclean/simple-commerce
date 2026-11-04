<?php

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderShipped;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\UseDatabaseContentDrivers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Statamic\Facades\Role;
use Statamic\Facades\User;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);
uses(UseDatabaseContentDrivers::class);

it('can resend notifications as a user who can edit orders', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'order_dispatched' => [
            CustomerOrderShipped::class => ['to' => 'customer'],
        ],
    ]);

    Role::make('order-editor')->permissions(['access cp', 'view orders', 'edit orders'])->save();

    $customer = Customer::make()->email('foo@example.com')->save();
    $order = Order::make()->customer($customer)->save();

    actingAs(tap(User::make()->assignRole('order-editor'))->save())
        ->post('/cp/simple-commerce/resend-notifications', [
            'status' => 'dispatched',
            'order_id' => $order->id,
        ])
        ->assertOk();

    Notification::assertSentTimes(CustomerOrderShipped::class, 1);
});

it('cant resend notifications as a user who can only view orders', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'order_dispatched' => [
            CustomerOrderShipped::class => ['to' => 'customer'],
        ],
    ]);

    Role::make('order-viewer')->permissions(['access cp', 'view orders'])->save();

    $customer = Customer::make()->email('foo@example.com')->save();
    $order = Order::make()->customer($customer)->save();

    actingAs(tap(User::make()->assignRole('order-viewer'))->save())
        ->post('/cp/simple-commerce/resend-notifications', [
            'status' => 'dispatched',
            'order_id' => $order->id,
        ])
        ->assertForbidden();

    Notification::assertNothingSent();
});
