<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use Carbon\CarbonPeriod;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Runway;
use Illuminate\Support\Carbon;
use Statamic\Facades\Collection;
use Statamic\Widgets\Widget;

class OrdersChart extends Widget
{
    public function html()
    {
        $indexUrl = null;

        if ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $indexUrl = cp_route('collections.show', SimpleCommerce::orderDriver()['collection']);
        } elseif ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $indexUrl = cp_route('runway.index', ['resourceHandle' => Runway::orderModel()->handle()]);
        }

        $timePeriod = CarbonPeriod::create(Carbon::now()->subDays(30)->format('Y-m-d'), Carbon::now()->format('Y-m-d'));

        $data = collect($timePeriod)->map(function ($date) {
            if ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
                $query = Collection::find(SimpleCommerce::orderDriver()['collection'])
                    ->queryEntries()
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->whereDate('status_log->paid', $date->format('d-m-Y'))
                    ->get();
            }

            if ((new self)->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
                $orderModel = new (SimpleCommerce::orderDriver()['model']);

                $query = $orderModel::query()
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->whereDate('data->status_log->paid', $date)
                    ->get();
            }

            return [
                'date' => $date->format('d-m-Y'),
                'count' => $query->count(),
            ];
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
