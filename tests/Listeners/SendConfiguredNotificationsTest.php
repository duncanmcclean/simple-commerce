<?php

use DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated;
use DuncanMcClean\SimpleCommerce\Events\PaymentStatusUpdated;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Listeners\SendConfiguredNotifications;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events\AnotherRandomEvent;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events\AnotherRandomEventNotification;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events\OrderDispatchedNotification;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events\OrderPlacedNotification;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events\PaymentRefundedNotification;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events\SomeRandomEvent;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events\SomeRandomEventNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification as NotificationFacade;

test('can send configured notification to customer', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'order_placed' => [
            OrderPlacedNotification::class => [
                'to' => 'customer',
            ],
        ],
    ]);

    $customer = Customer::make()->email($email = 'cj.cregg@example.com');
    $customer->save();

    $order = Order::make()->customer($customer);
    $order->save();

    $event = new OrderStatusUpdated(
        $order,
        OrderStatus::Placed
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        OrderPlacedNotification::class,
        function (OrderPlacedNotification $notification, array $channels, object $notifiable) use ($email) {
            return $notifiable->routes['mail'] === $email;
        }
    );
});

test('can send configured notification to order email', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'order_placed' => [
            OrderPlacedNotification::class => [
                'to' => 'customer',
            ],
        ],
    ]);

    $order = Order::make()->data(['email' => $email = 'leo.mcgarry@example.com']);
    $order->save();

    $event = new OrderStatusUpdated(
        $order,
        OrderStatus::Placed
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        OrderPlacedNotification::class,
        function (OrderPlacedNotification $notification, array $channels, object $notifiable) use ($email) {
            return $notifiable->routes['mail'] === $email;
        }
    );
});

test('cant send configured notification to customer when no customer exists on the order', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'order_placed' => [
            OrderPlacedNotification::class => [
                'to' => 'customer',
            ],
        ],
    ]);

    $order = Order::make();
    $order->save();

    $event = new OrderStatusUpdated(
        $order,
        OrderStatus::Placed
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemandTimes(
        OrderPlacedNotification::class,
        0
    );
});

test('can send configured notification to hard coded email address', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'order_placed' => [
            OrderPlacedNotification::class => [
                'to' => $email = 'duncan@example.com',
            ],
        ],
    ]);

    $order = Order::make();
    $order->save();

    $event = new OrderStatusUpdated(
        $order,
        OrderStatus::Placed
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        OrderPlacedNotification::class,
        function (OrderPlacedNotification $notification, array $channels, object $notifiable) use ($email) {
            return $notifiable->routes['mail'] === $email;
        }
    );
});

test('can send configured notification to hard coded email address with antlers', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'order_placed' => [
            OrderPlacedNotification::class => [
                'to' => '{{ some_random_email_field }}',
            ],
        ],
    ]);

    $order = Order::make()->merge(['some_random_email_field' => $email = 'josh.lyman@example.com']);
    $order->save();

    $event = new OrderStatusUpdated(
        $order,
        OrderStatus::Placed
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        OrderPlacedNotification::class,
        function (OrderPlacedNotification $notification, array $channels, object $notifiable) use ($email) {
            return $notifiable->routes['mail'] === $email;
        }
    );
});

test('can send configured notification for order status event', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'order_dispatched' => [
            OrderDispatchedNotification::class => [
                'to' => $email = 'random@email.com',
            ],
        ],
    ]);

    $customer = Customer::make();
    $customer->save();

    $order = Order::make()->customer($customer);
    $order->save();

    $event = new OrderStatusUpdated(
        $order,
        OrderStatus::Dispatched
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        OrderDispatchedNotification::class,
        function (OrderDispatchedNotification $notification, array $channels, object $notifiable) use ($email) {
            return $notifiable->routes['mail'] === $email;
        }
    );
});

test('can send configured notification for payment status event', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'order_refunded' => [
            PaymentRefundedNotification::class => [
                'to' => $email = 'random@email.com',
            ],
        ],
    ]);

    $customer = Customer::make();
    $customer->save();

    $order = Order::make()->customer($customer);
    $order->save();

    $event = new PaymentStatusUpdated(
        $order,
        PaymentStatus::Refunded
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        PaymentRefundedNotification::class,
        function (PaymentRefundedNotification $notification, array $channels, object $notifiable) use ($email) {
            return $notifiable->routes['mail'] === $email;
        }
    );
});

test('can send configured notification for some random event', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'some_random_event' => [
            SomeRandomEventNotification::class => [
                'to' => $email = 'random@email.com',
            ],
        ],
    ]);

    $customer = Customer::make();
    $customer->save();

    $order = Order::make()->customer($customer);
    $order->save();

    $event = new SomeRandomEvent(
        $order
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        SomeRandomEventNotification::class,
        function (SomeRandomEventNotification $notification, array $channels, object $notifiable) use ($email) {
            return $notifiable->routes['mail'] === $email;
        }
    );
});

test('can send configured notification and ensure all events defined in notifications constructor are passed along', function () {
    NotificationFacade::fake();

    Config::set('simple-commerce.notifications', [
        'another_random_event' => [
            AnotherRandomEventNotification::class => [
                'to' => $email = 'random@email.com',
            ],
        ],
    ]);

    $customer = Customer::make();
    $customer->save();

    $order = Order::make()->customer($customer);
    $order->save();

    $event = new AnotherRandomEvent(
        $order,
        'foo',
        true
    );

    (new SendConfiguredNotifications())->handle($event);

    NotificationFacade::assertSentOnDemand(
        AnotherRandomEventNotification::class,
        function (AnotherRandomEventNotification $notification, array $channels, object $notifiable) use ($email, $order) {
            return $notifiable->routes['mail'] === $email
               && $notification->order === $order
               && $notification->somethingElseThatIsAProperty === true
               && ! property_exists($notification, 'someOtherProperty');
        }
    );
});
