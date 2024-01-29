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
use Statamic\Facades\Site;
use Statamic\Widgets\Widget;

class RecentOrders extends Widget
{
    public function html()
    {
        $indexUrl = $this->getIndexUrl();

        $recentOrders = Order::query()
            ->wherePaymentStatus(PaymentStatus::Paid)
            ->orderBy('status_log->paid', 'desc')
            ->limit($this->config('limit', 5))
            ->get()
            ->map(function ($order) {
                return [
                    'order_number' => $order->orderNumber,
                    'grand_total' => Currency::parse($order->grandTotal(), Site::selected()),
                    'edit_url' => $this->getEditUrl($order),
                    'date' => $order->statusLog()
                        ->filter(fn (StatusLogEvent $statusLogEvent) => $statusLogEvent->status->is(PaymentStatus::Paid))
                        ->last()
                        ->date()
                        ->format(config('statamic.system.date_format')),
                ];
            })
            ->values();

        return view('simple-commerce::cp.widgets.recent-orders', [
            'url' => $indexUrl,
            'recentOrders' => $recentOrders,
        ]);
    }

    protected function getIndexUrl(): string
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return cp_route('collections.show', SimpleCommerce::orderDriver()['collection']);
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            return cp_route('runway.index', ['resourceHandle' => Runway::orderModel()->handle()]);
        }
    }

    protected function getEditUrl($order): string
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $order->resource()->editUrl();
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            return cp_route('runway.edit', [
                'resourceHandle' => Runway::orderModel()->handle(),
                'record' => $order->resource()->{$order->getRouteKeyName()},
            ]);
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
