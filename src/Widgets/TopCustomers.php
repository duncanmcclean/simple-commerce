<?php

namespace DuncanMcClean\SimpleCommerce\Widgets;

use DuncanMcClean\SimpleCommerce\Customers\EloquentCustomerRepository;
use DuncanMcClean\SimpleCommerce\Customers\EntryCustomerRepository;
use DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Illuminate\Database\SQLiteConnection;
use Statamic\Widgets\Widget;

class TopCustomers extends Widget
{
    public function html()
    {
        $indexUrl = $this->getIndexUrl();

        $isUsingEloquentRepository = $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EloquentCustomerRepository::class);
        $isUsingEloquentUsersRepository = $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], UserCustomerRepository::class)
            && config('statamic.users.repository') === 'eloquent';

        $topCustomers = Customer::query()
            ->when($isUsingEloquentRepository, function ($query) {
                $query
                    ->withCount('orders')
                    ->orderBy('orders_count', 'desc')
                    ->limit($this->config('limit', 5));
            })
            ->when($isUsingEloquentUsersRepository, function ($query) {
                $query
                    ->where('orders', '!=', null)
                    ->orderBy(function ($query) {
                        $query->when($query->connection instanceof SQLiteConnection, function ($query) {
                            $query->selectRaw('JSON_ARRAY_LENGTH(orders)');
                        }, function ($query) {
                            $query->selectRaw('JSON_LENGTH(orders)');
                        });
                    }, 'desc')
                    ->limit($this->config('limit', 5));
            })
            ->when(! $isUsingEloquentRepository && ! $isUsingEloquentUsersRepository, function ($query) {
                $query
                    ->where('orders', '!=', null)
                    ->orderByDesc('orders')
                    ->limit($this->config('limit', 5));
            })
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id(),
                    'email' => $customer->email(),
                    // 'edit_url' => $customer->resource()->editUrl(),
                    'edit_url' => '#',
                    'orders_count' => count($customer->get('orders', [])),
                ];
            });

        return view('simple-commerce::cp.widgets.top-customers', [
            'url' => $indexUrl,
            'topCustomers' => $topCustomers,
        ]);
    }

    protected function getIndexUrl(): string
    {
        if ((new self)->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EntryCustomerRepository::class)) {
            return cp_route('collections.show', SimpleCommerce::customerDriver()['collection']);
        }

        if ((new self)->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EloquentCustomerRepository::class)) {
            return cp_route('runway.index', ['resource' => Runway::customerModel()->handle()]);
        }

        if ((new self)->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], UserCustomerRepository::class)) {
            return cp_route('users.index');
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
