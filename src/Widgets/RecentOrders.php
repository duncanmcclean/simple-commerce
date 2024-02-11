<?php

namespace DuncanMcClean\SimpleCommerce\Widgets;

use DuncanMcClean\SimpleCommerce\Currency;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Orders\StatusLogEvent;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Support\Runway;
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
                        ->first()
                        ?->date()
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
            return cp_route('runway.index', ['resource' => Runway::orderModel()->handle()]);
        }
    }

    protected function getEditUrl($order): string
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $order->resource()->editUrl();
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            return cp_route('runway.edit', [
                'resource' => Runway::orderModel()->handle(),
                'model' => $order->resource()->{$order->resource()->getRouteKeyName()},
            ]);
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
