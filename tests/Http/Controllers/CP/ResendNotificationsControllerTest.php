<?php

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid;
use DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderShipped;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Statamic\Facades\User;

use function Pest\Laravel\actingAs;

uses(SetupCollections::class);

it('can resend notifications for order statuses', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'order_dispatched' => [
            \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderShipped::class => ['to' => 'customer'],
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
            \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid::class => ['to' => 'customer'],
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
