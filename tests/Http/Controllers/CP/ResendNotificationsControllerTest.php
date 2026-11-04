<?php

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid;
use DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderShipped;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Statamic\Facades\Role;
use Statamic\Facades\User;

use function Pest\Laravel\actingAs;

uses(SetupCollections::class);

it('can resend notifications for order statuses', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'order_dispatched' => [
            CustomerOrderShipped::class => ['to' => 'customer'],
        ],
    ]);

    $customer = Customer::make()->email('foo@example.com')->save();
    $order = Order::make()->customer($customer)->save();

    actingAs(User::make()->makeSuper()->save())
        ->post('/cp/simple-commerce/resend-notifications', [
            'status' => 'dispatched',
            'order_id' => $order->id,
        ])
        ->assertOk();

    Notification::assertSentTimes(CustomerOrderShipped::class, 1);
});

it('can resend notifications for payment statuses', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'order_paid' => [
            CustomerOrderPaid::class => ['to' => 'customer'],
        ],
    ]);

    $customer = Customer::make()->email('foo@example.com')->save();
    $order = Order::make()->customer($customer)->save();

    actingAs(User::make()->makeSuper()->save())
        ->post('/cp/simple-commerce/resend-notifications', [
            'status' => 'paid',
            'order_id' => $order->id,
        ])
        ->assertOk();

    Notification::assertSentTimes(CustomerOrderPaid::class, 1);
});

it('can resend notifications as a user who can edit orders', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'order_dispatched' => [
            CustomerOrderShipped::class => ['to' => 'customer'],
        ],
    ]);

    Role::make('order-editor')->permissions(['access cp', 'view orders entries', 'edit orders entries'])->save();

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

    Role::make('order-viewer')->permissions(['access cp', 'view orders entries'])->save();

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
