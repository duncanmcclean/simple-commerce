<?php

namespace DuncanMcClean\SimpleCommerce;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Stache\Query\CartQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Query\OrderQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Stores\CartsStore;
use DuncanMcClean\SimpleCommerce\Stache\Stores\OrdersStore;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        Console\Commands\MigrateOrders::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\MoneyFieldtype::class,
        Fieldtypes\OrdersFieldtype::class,
        Fieldtypes\ProductVariantsFieldtype::class,
    ];

    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            Listeners\AssignUserAsCustomer::class,
        ],
        \Illuminate\Auth\Events\Logout::class => [
            Listeners\RemoveUserAsCustomer::class,
        ],
        \Statamic\Events\UserBlueprintFound::class => [
            Listeners\EnsureUserFields::class,
        ],
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $tags = [
        Tags\SimpleCommerceTag::class,
    ];

    protected $vite = [
        'hotFile' => 'vendor/simple-commerce/hot',
        'publicDirectory' => 'dist',
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
    ];

    public function bootAddon()
    {
        // TODO: Make these paths configurable.
        $this->app['stache']->registerStores([
            (new CartsStore)->directory(storage_path('statamic/simple-commerce/carts')),
            (new OrdersStore)->directory(base_path('content/orders'))
        ]);

        $this->app->bind(CartQueryBuilder::class, function () {
            return new CartQueryBuilder($this->app->make(Stache::class)->store('carts'));
        });

        $this->app->bind(OrderQueryBuilder::class, function () {
            return new OrderQueryBuilder($this->app->make(Stache::class)->store('orders'));
        });

        collect([
            \DuncanMcClean\SimpleCommerce\Contracts\Cart\CartRepository::class => \DuncanMcClean\SimpleCommerce\Stache\Repositories\CartRepository::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository::class => \DuncanMcClean\SimpleCommerce\Stache\Repositories\OrderRepository::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Products\ProductRepository::class => \DuncanMcClean\SimpleCommerce\Products\ProductRepository::class,
        ])->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

        Nav::extend(function ($nav) {
            $nav->create('Orders')
                ->section(__('Simple Commerce'))
                ->route('simple-commerce.orders.index')
                ->icon(SimpleCommerce::svg('shop'));
        });

        User::computed('orders', function ($user) {
            return Order::query()->pluck('id')->all(); // TODO: how can we filter by customer if $customer is a User instance?
        });
    }
}
