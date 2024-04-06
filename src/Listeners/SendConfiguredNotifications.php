<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated;
use DuncanMcClean\SimpleCommerce\Events\PaymentStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionParameter;
use Statamic\Facades\Antlers;

class SendConfiguredNotifications implements ShouldQueue
{
    public function handle($event)
    {
        $notifications = collect(Config::get('simple-commerce.notifications'))
            ->get($this->getEventName($event));

        if (! $notifications) {
            return;
        }

        $locale = $event->order->site()
            ? $event->order->site()->locale()
            : config('app.locale');

        foreach ($notifications as $notification => $config) {
            $freshNotification = null;

            $notifiables = $this->getNotifiables($config, $notification, $event);

            $notification = new $notification(...$this->getNotificationParameters($config, $notification, $event));
            $notification->locale($locale);

            if (! $notifiables) {
                break;
            }

            foreach ($notifiables as $notifiable) {
                if (! $freshNotification) {
                    $freshNotification = Notification::route($notifiable['channel'], $notifiable['route']);
                } else {
                    $freshNotification->route($notifiable['channel'], $notifiable['route']);
                }
            }

            optional($freshNotification)->notify($notification);
        }
    }

    protected function getEventName($event): string
    {
        // If the event is OrderStatusUpdated, then we want to use the
        // order's status as the "event name".
        if ($event instanceof OrderStatusUpdated) {
            $orderStatus = $event->orderStatus;

            return "order_{$orderStatus->value}";
        }

        // If the event is PaymentStatusUpdated, then we want to use the
        // order's payment status as the "event name".
        if ($event instanceof PaymentStatusUpdated) {
            $paymentStatus = $event->paymentStatus;

            return "order_{$paymentStatus->value}";
        }

        $eventName = Str::of(get_class($event))
            ->afterLast('\\')
            ->snake()
            ->__toString();

        return $eventName;
    }

    protected function getNotifiables(array $config, $notification, $event): ?array
    {
        if ($config['to'] === 'customer') {
            if ($customer = $event->order->customer()) {
                return [
                    ['channel' => 'mail', 'route' => $customer->email()],
                ];
            }

            if ($email = $event->order->get('email')) {
                return [
                    ['channel' => 'mail', 'route' => $email],
                ];
            }

            // When there's no customer on the order
            return null;
        }

        if (is_string($config['to'])) {
            return [
                [
                    'channel' => 'mail',
                    'route' => (string) Antlers::parse($config['to'], $event->order->toAugmentedArray()),
                ],
            ];
        }

        throw new \Exception("No notifiable specified for [{$notification}]");
    }

    protected function getNotificationParameters(array $config, $notification, $event): array
    {
        $reflection = new ReflectionClass($notification);
        $constructor = $reflection->getConstructor();

        return collect($constructor->getParameters())
            ->map(function (ReflectionParameter $parameter) use ($event) {
                if (property_exists($event, $parameter->getName())) {
                    return $event->{$parameter->getName()};
                }

                throw new \Exception("A parameter called [{$parameter->getName()}] does not exist on the event.");
            })
            ->toArray();
    }
}
