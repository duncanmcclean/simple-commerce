<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\StatusLogEvent;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Runway;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Widgets\Widget;

class RecentOrders extends Widget
{
    public function html()
    {
        $recentOrders = null;

        if ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $recentOrders = Collection::find(SimpleCommerce::orderDriver()['collection'])
                ->queryEntries()
                ->where('payment_status', PaymentStatus::Paid->value)
                ->orderBy('status_log->paid', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($entry) {
                    $order = Order::find($entry->id());

                    return [
                        'order_number' => $order->orderNumber,
                        'grand_total' => Currency::parse($order->grandTotal(), Site::selected()),
                        'edit_url' => $entry->editUrl(),
                        'date' => $order->statusLog()
                            ->filter(fn (StatusLogEvent $statusLogEvent) => $statusLogEvent->status->is(PaymentStatus::Paid))
                            ->last()
                            ->date()
                            ->format(config('statamic.system.date_format')),
                    ];
                })
                ->values();
        }

        if ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $recentOrders = Runway::orderModel()->model()->query()
                ->where('payment_status', PaymentStatus::Paid->value)
                ->orderBy('data->status_log->paid', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    $order = Order::find($order->id);

                    return [
                        'order_number' => $order->orderNumber,
                        'grand_total' => Currency::parse($order->grandTotal(), Site::selected()),
                        'edit_url' => cp_route('runway.edit', [
                            'resourceHandle' => Runway::orderModel()->handle(),
                            'record' => $order->resource()->{$order->getRouteKeyName()},
                        ]),
                    ];
                })
                ->values();
        }

        if (! $recentOrders) {
            throw new \Exception("Recent Orders widget only supports Entry & Database order drivers.");
        }

        return view('simple-commerce::cp.widgets.recent-orders', [
            'url' => '#', // TODO
            'recentOrders' => $recentOrders,
        ]);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
