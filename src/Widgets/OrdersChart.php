<?php

namespace DuncanMcClean\SimpleCommerce\Widgets;

use Carbon\CarbonPeriod;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Illuminate\Support\Carbon;
use Statamic\Widgets\Widget;

class OrdersChart extends Widget
{
    public function html()
    {
        $indexUrl = null;

        if ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $indexUrl = cp_route('collections.show', SimpleCommerce::orderDriver()['collection']);
        } elseif ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $indexUrl = cp_route('runway.index', ['resource' => Runway::orderModel()->handle()]);
        }

        $timePeriod = CarbonPeriod::create(Carbon::now()->subDays(30)->format('Y-m-d'), Carbon::now()->format('Y-m-d'));

        $data = collect($timePeriod)->map(function ($date) {
            $ordersCount = Order::query()
                ->wherePaymentStatus(PaymentStatus::Paid)
                ->whereStatusLogDate(PaymentStatus::Paid, $date)
                ->count();

            return ['date' => $date->format('d-m-Y'), 'count' => $ordersCount];
        });

        if (! $data) {
            throw new \Exception('Orders Chart widget is not compatible with the configured customer repository.');
        }

        return view('simple-commerce::cp.widgets.orders-chart', [
            'url' => $indexUrl,
            'data' => $data,
        ]);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
