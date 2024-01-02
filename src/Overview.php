<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Carbon\CarbonPeriod;
use DoubleThreeDigital\SimpleCommerce\Customers\EloquentCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\StatusLogEvent;
use DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository;
use DoubleThreeDigital\SimpleCommerce\Support\Runway;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class Overview
{
    protected static $widgets = [];

    public static function widgets(): array
    {
        return static::$widgets;
    }

    public static function widget(string $handle): ?array
    {
        return collect(static::$widgets)->firstWhere('handle', $handle);
    }

    public static function registerWidget(string $handle, array $config, \Closure $callback)
    {
        static::$widgets[] = array_merge($config, [
            'handle' => $handle,
            'callback' => $callback,
        ]);
    }

    public static function bootCoreWidgets()
    {
        static::registerWidget(
            'orders-chart',
            [
                'name' => __('Orders Chart'),
                'component' => 'overview-orders-chart',
            ],
            function (Request $request) {
                $timePeriod = CarbonPeriod::create(now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d'));

                return collect($timePeriod)->map(function ($date) {
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
            }
        );
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
