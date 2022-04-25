<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
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
                'name' => 'Orders Chart',
                'component' => 'overview-orders-chart',
            ],
            function (Request $request) {
                $timePeriod = CarbonPeriod::create(now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d'));

                return collect($timePeriod)->map(function ($date) {
                    if (isset(SimpleCommerce::orderDriver()['collection'])) {
                        $query = Collection::find(SimpleCommerce::orderDriver()['collection'])
                            ->queryEntries()
                            ->where('is_paid', true)
                            ->whereDate('paid_date', $date->format('d-m-Y'))
                            ->get();
                    }

                    if (isset(SimpleCommerce::orderDriver()['model'])) {
                        $orderModel = new (SimpleCommerce::orderDriver()['model']);

                        $query = $orderModel::query()
                            ->where('is_paid', true)
                            ->whereDate('paid_date', $date)
                            ->get();
                    }

                    return [
                        'date' =>  $date->format('d-m-Y'),
                        'count' => $query->count(),
                    ];
                });
            }
        );

        static::registerWidget(
            'recent-orders',
            [
                'name' => 'Recent Orders',
                'component' => 'overview-recent-orders',
            ],
            function (Request $request) {
                if (isset(SimpleCommerce::orderDriver()['collection'])) {
                    $query = Collection::find(SimpleCommerce::orderDriver()['collection'])
                        ->queryEntries()
                        ->where('is_paid', true)
                        ->orderBy('paid_date', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($entry) {
                            return Order::find($entry->id());
                        })
                        ->values();

                    return $query->map(function ($order) {
                        return [
                            'id' => $order->id(),
                            'order_number' => $order->orderNumber(),
                            'edit_url' => $order->resource()->editUrl(),
                            'grand_total' => Currency::parse($order->grandTotal(), Site::selected()),
                            'paid_date' => Carbon::parse($order->get('paid_date'))->format(config('statamic.system.date_format')),
                        ];
                    });
                }

                if (isset(SimpleCommerce::orderDriver()['model'])) {
                    $orderModel = new (SimpleCommerce::orderDriver()['model']);

                    $query = $orderModel::query()
                        ->where('is_paid', true)
                        ->orderBy('paid_date', 'desc')
                        ->orderBy('data->paid_date', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($order) {
                            return Order::find($order->id);
                        })
                        ->values();

                    return $query->map(function ($order) use ($orderModel) {
                        return [
                            'id' => $order->id(),
                            'order_number' => $order->orderNumber(),
                            'edit_url' => cp_route('runway.edit', [
                                'resourceHandle' => \DoubleThreeDigital\Runway\Runway::findResourceByModel($orderModel)->handle(),
                                'record' => $order->resource()->{$orderModel->getRouteKeyName()},
                            ]),
                            'grand_total' => Currency::parse($order->grandTotal(), Site::selected()),
                            'paid_date' => Carbon::parse($order->get('paid_date'))->format(config('statamic.system.date_format')),
                        ];
                    });
                }

                return null;
            },
        );

        static::registerWidget(
            'top-customers',
            [
                'name' => 'Top Customers',
                'component' => 'overview-top-customers',
            ],
            function (Request $request) {
                if (isset(SimpleCommerce::customerDriver()['collection'])) {
                    $query = Collection::find(SimpleCommerce::customerDriver()['collection'])
                        ->queryEntries()
                        ->get()
                        ->sortByDesc(function ($customer) {
                            return count($customer->get('orders', []));
                        })
                        ->take(5)
                        ->map(function ($entry) {
                            return Customer::find($entry->id());
                        })
                        ->values();

                    return $query->map(function ($customer) {
                        return [
                            'id' => $customer->id(),
                            'email' => $customer->email(),
                            'edit_url' => $customer->resource()->editUrl(),
                            'orders_count' => count($customer->get('orders', [])),
                        ];
                    });
                }

                if (isset(SimpleCommerce::customerDriver()['model'])) {
                    $customerModel = new (SimpleCommerce::customerDriver()['model']);

                    $query = $customerModel::query()
                        ->whereHas('orders', function ($query) {
                            $query->where('is_paid', true);
                        })
                        ->withCount('orders')
                        ->orderBy('orders_count', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($customer) {
                            return Customer::find($customer->id);
                        })
                        ->values();

                    return $query->map(function ($customer) use ($customerModel) {
                        return [
                            'id' => $customer->id(),
                            'email' => $customer->email(),
                            'edit_url' => cp_route('runway.edit', [
                                'resourceHandle' => \DoubleThreeDigital\Runway\Runway::findResourceByModel($customerModel)->handle(),
                                'record' => $customer->resource()->{$customerModel->getRouteKeyName()},
                            ]),
                            'orders_count' => $customer->orders()->count(),
                        ];
                    });
                }

                $query = User::all()
                    ->where('orders', '!=', null)
                    ->sortByDesc(function ($customer) {
                        return count($customer->get('orders', []));
                    })
                    ->take(5)
                    ->map(function ($user) {
                        return Customer::find($user->id());
                    })
                    ->values();

                return $query->map(function ($customer) {
                    return [
                        'id' => $customer->id(),
                        'email' => $customer->email(),
                        'edit_url' => cp_route('users.edit', [
                            'user' => $customer->id(),
                        ]),
                        'orders_count' => count($customer->get('orders', [])),
                    ];
                });
            },
        );

        static::registerWidget(
            'low-stock-products',
            [
                'name' => 'Low Stock Products',
                'component' => 'overview-low-stock-products',
            ],
            function (Request $request) {
                if (isset(SimpleCommerce::productDriver()['collection'])) {
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
}
