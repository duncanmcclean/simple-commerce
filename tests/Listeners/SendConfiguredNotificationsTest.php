<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Listeners;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\PaymentStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendConfiguredNotifications;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendConfiguredNotificationsTest extends TestCase
{
    use SetupCollections;

    /** @test */
    public function can_send_configured_notification_to_customer()
    {
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
    }

    /** @test */
    public function can_send_configured_notification_to_order_email()
    {
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
    }

    /** @test */
    public function cant_send_configured_notification_to_customer_when_no_customer_exists_on_the_order()
    {
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
    }

    /** @test */
    public function can_send_configured_notification_to_hard_coded_email_address()
    {
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
    }

    /** @test */
    public function can_send_configured_notification_to_hard_coded_email_address_with_antlers()
    {
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
    }

    /** @test */
    public function can_send_configured_notification_for_order_status_event()
    {
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
    }

    /** @test */
    public function can_send_configured_notification_for_payment_status_event()
    {
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
    }

     /** @test */
     public function can_send_configured_notification_for_some_random_event()
     {
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
     }

     /** @test */
     public function can_send_configured_notification_and_ensure_all_events_defined_in_notifications_constructor_are_passed_along()
     {
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
     }
}

class OrderPlacedNotification extends Notification
{
    public function __construct(protected OrderContract $order)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}


class OrderDispatchedNotification extends Notification
{
    public function __construct(protected OrderContract $order)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}

class PaymentRefundedNotification extends Notification
{
    public function __construct(protected OrderContract $order)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}

class SomeRandomEvent
{
    public function __construct(public OrderContract $order)
    {
    }
}

class SomeRandomEventNotification extends Notification
{
    public function __construct(protected OrderContract $order)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}

class AnotherRandomEvent
{
    public function __construct(public OrderContract $order, public string $someOtherProperty, public bool $somethingElseThatIsAProperty)
    {
    }
}

class AnotherRandomEventNotification extends Notification
{
    public function __construct(public OrderContract $order, public bool $somethingElseThatIsAProperty)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}
