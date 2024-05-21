<?php

namespace DuncanMcClean\SimpleCommerce;

use Barryvdh\Debugbar\Facade as Debugbar;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Stache\Query\OrderQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Stores\OrdersStore;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Carbon;
use Statamic\CP\Navigation\NavItem;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Events\UserBlueprintFound;
use Statamic\Facades\Collection;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
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
        Fieldtypes\ProductVariantsFieldtype::class,
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
        $this->app['stache']->registerStore(
            (new OrdersStore)->directory(base_path('content/orders')) // todo: make this configurable
        );

        $this->app->bind(OrderQueryBuilder::class, function () {
            return new OrderQueryBuilder($this->app->make(Stache::class)->store('orders'));
        });

        collect([
            \DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository::class => \DuncanMcClean\SimpleCommerce\Stache\Repositories\OrderRepository::class,
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
    }
}
