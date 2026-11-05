<?php

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Statamic\Facades\User;

uses(SetupCollections::class, RefreshContent::class);

test('customer tags require authenticated ownership', function (string $template, string $expected, ?string $email, bool $users) {
    if ($users) {
        setupUserCustomerRepository();
    }

    try {
        $customer = Customer::make()->email('private@example.com')->data(['name' => 'Private Customer'])->save();
        $order = Order::make()->customer($customer->id())->set('note', 'Private Order Note')->save();
        $customer->set('orders', [$order->id()])->save();

        if ($email) {
            $this->actingAs(User::findByEmail($email) ?? User::make()->email($email)->save());
        }

        $template = str_replace(['CUSTOMER_ID', 'ORDER_ID'], [$customer->id(), $order->id()], $template);
        $output = (string) tag($template);

        if ($email === 'private@example.com') {
            expect($output)->toBe($expected);
        } else {
            expect($output)->toBe('');
        }
    } finally {
        if ($users) {
            tearDownUserCustomerRepository();
        }
    }
})->with([
    'profile' => ['{{ sc:customer id="CUSTOMER_ID" }}{{ name }}{{ /sc:customer }}', 'Private Customer'],
    'orders' => ['{{ sc:customer:orders customer="CUSTOMER_ID" }}{{ note }}{{ /sc:customer:orders }}', 'Private Order Note'],
    'order' => ['{{ sc:customer:order id="ORDER_ID" }}{{ note }}{{ /sc:customer:order }}', 'Private Order Note'],
])->with([
    'owner' => 'private@example.com',
    'guest' => null,
    'different user' => 'other@example.com',
])->with(['entries' => false, 'users' => true]);
