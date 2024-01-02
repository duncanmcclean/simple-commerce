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

        static::registerWidget(
            'low-stock-products',
            [
                'name' => __('Low Stock Products'),
                'component' => 'overview-low-stock-products',
            ],
            function (Request $request) {
                if ((new self)->isOrExtendsClass(SimpleCommerce::productDriver()['repository'], EntryProductRepository::class)) {
                    $query = Collection::find(SimpleCommerce::productDriver()['collection'])
                        ->queryEntries()
                        ->where('stock', '<', config('simple-commerce.low_stock_threshold'))
                        ->orderBy('stock', 'asc')
                        ->get()
                        ->reject(function ($entry) {
                            return $entry->has('product_variants')
                                || ! $entry->has('stock');
                        })
                        ->take(5)
                        ->map(function ($entry) {
                            return Product::find($entry->id());
                        })
                        ->values();

                    return $query->map(function ($product) {
                        return [
                            'id' => $product->id(),
                            'title' => $product->get('title'),
                            'stock' => $product->stock(),
                            'edit_url' => $product->resource()->editUrl(),
                        ];
                    });
                }

                return null;
            },
        );
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
