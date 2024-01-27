<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use DoubleThreeDigital\SimpleCommerce\Customers\EloquentCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Runway;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Widgets\Widget;

class TopCustomers extends Widget
{
    public function html()
    {
        $indexUrl = null;
        $topCustomers = null;

        // TODO: refactor query
        if ((new self)->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EntryCustomerRepository::class)) {
            $indexUrl = cp_route('collections.show', SimpleCommerce::customerDriver()['collection']);

            $topCustomers = Collection::find(SimpleCommerce::customerDriver()['collection'])
                ->queryEntries()
                ->get()
                ->sortByDesc(function ($customer) {
                    return count($customer->get('orders', []));
                })
                ->take($this->config('limit', 5))
                ->map(function ($entry) {
                    return Customer::find($entry->id());
                })
                ->values()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id(),
                        'email' => $customer->email(),
                        'edit_url' => $customer->resource()->editUrl(),
                        'orders_count' => count($customer->get('orders', [])),
                    ];
                });
        }

        if ((new self)->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EloquentCustomerRepository::class)) {
            $indexUrl = cp_route('runway.index', ['resourceHandle' => Runway::customerModel()->handle()]);

            $topCustomers = Runway::customerModel()->model()->query()
                ->whereHas('orders', function ($query) {
                    $query->where('payment_status', 'paid');
                })
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->limit($this->config('limit', 5))
                ->get()
                ->map(function ($customer) {
                    return Customer::find($customer->id);
                })
                ->values()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id(),
                        'email' => $customer->email(),
                        'edit_url' => cp_route('runway.edit', [
                            'resourceHandle' => Runway::customerModel()->handle(),
                            'record' => $customer->resource()->{Runway::customerModel()->model()->getRouteKeyName()},
                        ]),
                        'orders_count' => $customer->orders()->count(),
                    ];
                });
        }

        if ((new self)->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], UserCustomerRepository::class)) {
            if (config('statamic.users.repository') === 'eloquent') {
                $indexUrl = cp_route('users.index');

                $userModelClass = config('auth.providers.users.model');
                $userModel = new $userModelClass;

                $topCustomers = $userModel::query()
                    ->where('orders', '!=', null)
                    ->orderBy(function ($query) {
                        $query->selectRaw('JSON_ARRAY_LENGTH(orders)');
                    }, 'desc')
                    ->limit($this->config('limit', 5))
                    ->get()
                    ->map(function ($model) {
                        $customer = Customer::find($model->id);

                        return [
                            'id' => $customer->id(),
                            'email' => $customer->email(),
                            'edit_url' => cp_route('users.edit', ['user' => $customer->id()]),
                            'orders_count' => count($customer->get('orders', [])),
                        ];
                    });
            } else {
                $indexUrl = cp_route('users.index');

                $topCustomers = User::all()
                    ->where('orders', '!=', null)
                    ->sortByDesc(function ($customer) {
                        return count($customer->get('orders', []));
                    })
                    ->take($this->config('limit', 5))
                    ->map(function ($user) {
                        $customer = Customer::find($user->id());

                        return [
                            'id' => $customer->id(),
                            'email' => $customer->email(),
                            'edit_url' => cp_route('users.edit', ['user' => $customer->id()]),
                            'orders_count' => count($customer->get('orders', [])),
                        ];
                    })
                    ->values();
            }
        }

        if (! $topCustomers) {
            throw new \Exception('Top Customers widget is not compatible with the configured customer repository.');
        }

        return view('simple-commerce::cp.widgets.top-customers', [
            'url' => $indexUrl,
            'topCustomers' => $topCustomers,
        ]);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
