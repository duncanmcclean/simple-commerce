<?php

namespace DuncanMcClean\SimpleCommerce;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Jobs\PurgeAbandonedCarts;
use DuncanMcClean\SimpleCommerce\Stache\Query\CartQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Query\CouponQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Query\OrderQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Stores\CartsStore;
use DuncanMcClean\SimpleCommerce\Stache\Stores\CouponsStore;
use DuncanMcClean\SimpleCommerce\Stache\Stores\OrdersStore;
use Illuminate\Console\Scheduling\Schedule;
use Statamic\Console\Commands\Multisite as MultisiteCommand;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Config;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\File;
use Statamic\Facades\Permission;
use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $config = false;

    // TODO: AddonServiceProvider::bootScopes() only autoloads from src/Scopes, not src/Scopes/Filters.
    protected $scopes = [
        Scopes\Filters\CouponType::class,
        Scopes\Filters\OrderSite::class,
        Scopes\Filters\OrderStatus::class,
    ];

    protected $vite = [
        //        'hotFile' => 'vendor/simple-commerce/dist/hot',
        'hotFile' => 'vendor/simple-commerce/hot',
        'publicDirectory' => 'dist',
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
    ];

    protected array $shippingMethods = [
        Shipping\FreeShipping::class,
    ];

    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'statamic.simple-commerce');

        $this->publishes([
            __DIR__.'/../config/simple-commerce.php' => config_path('statamic/simple-commerce.php'),
        ], 'simple-commerce-config');

        $this->publishes([
            __DIR__.'/../resources/views/checkout-stubs' => resource_path('views/checkout'),
        ], 'simple-commerce-checkout-stubs');

        $this->app['stache']->registerStores([
            (new CartsStore)->directory(config('statamic.simple-commerce.carts.directory')),
            (new CouponsStore)->directory(config('statamic.simple-commerce.coupons.directory')),
            (new OrdersStore)->directory(config('statamic.simple-commerce.orders.directory')),
        ]);

        $this->app->bind(CartQueryBuilder::class, function () {
            return new CartQueryBuilder($this->app->make(Stache::class)->store('carts'));
        });

        $this->app->bind(CouponQueryBuilder::class, function () {
            return new CouponQueryBuilder($this->app->make(Stache::class)->store('coupons'));
        });

        $this->app->bind(OrderQueryBuilder::class, function () {
            return new OrderQueryBuilder($this->app->make(Stache::class)->store('orders'));
        });

        collect([
            \DuncanMcClean\SimpleCommerce\Contracts\Cart\CartRepository::class => \DuncanMcClean\SimpleCommerce\Stache\Repositories\CartRepository::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Coupons\CouponRepository::class => \DuncanMcClean\SimpleCommerce\Stache\Repositories\CouponRepository::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository::class => \DuncanMcClean\SimpleCommerce\Stache\Repositories\OrderRepository::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Products\ProductRepository::class => \DuncanMcClean\SimpleCommerce\Products\ProductRepository::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClassRepository::class => \DuncanMcClean\SimpleCommerce\Taxes\TaxClassRepository::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxZoneRepository::class => \DuncanMcClean\SimpleCommerce\Taxes\TaxZoneRepository::class,
        ])->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

        if (config('statamic.simple-commerce.orders.repository') === 'eloquent') {
            $this->app->bind('simple-commerce.orders.eloquent.model', function () {
                return config('statamic.simple-commerce.orders.model', \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel::class);
            });

            Statamic::repository(\DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository::class, \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderRepository::class);
        }

        foreach ($this->shippingMethods as $shippingMethod) {
            $shippingMethod::register();
        }

        $this->app->bind(Contracts\Taxes\Driver::class, Taxes\DefaultTaxDriver::class);

        $this->app->make(Schedule::class)->job(PurgeAbandonedCarts::class)->daily();

        Nav::extend(function ($nav) {
            $nav->create(__('Orders'))
                ->section(__('Simple Commerce'))
                ->route('simple-commerce.orders.index')
                ->icon(SimpleCommerce::svg('shop'))
                ->can('view orders');

            $nav->create(__('Coupons'))
                ->section(__('Simple Commerce'))
                ->route('simple-commerce.coupons.index')
                ->icon('tags')
                ->can('view coupons');

            if (SimpleCommerce::usingDefaultTaxDriver()) {
                $nav->create(__('Tax Classes'))
                    ->section(__('Simple Commerce'))
                    ->route('simple-commerce.tax-classes.index')
                    ->icon(SimpleCommerce::svg('money-cash-file-dollar'))
                    ->can('manage taxes');

                $nav->create(__('Tax Zones'))
                    ->section(__('Simple Commerce'))
                    ->route('simple-commerce.tax-zones.index')
                    ->icon(SimpleCommerce::svg('money-cash-file-dollar'))
                    ->can('manage taxes');
            }
        });

        Permission::extend(function () {
            Permission::group('simple-commerce', __('Simple Commerce'), function () {
                Permission::register('view coupons', function ($permission) {
                    $permission->label(__('View Coupons'));

                    $permission->children([
                        Permission::make('edit coupons')->label(__('Edit Coupons'))->children([
                            Permission::make('create coupons')->label(__('Create Coupons')),
                            Permission::make('delete coupons')->label(__('Delete Coupons')),
                        ]),
                    ]);
                });

                Permission::register('view orders', function ($permission) {
                    $permission->label(__('View Orders'));

                    $permission->children([
                        Permission::make('edit orders')->label(__('Edit Orders')),
                        Permission::make('refund orders')->label(__('Refund Orders')),
                    ]);
                });

                if (SimpleCommerce::usingDefaultTaxDriver()) {
                    Permission::register('manage taxes')->label(__('Manage Taxes'));
                }
            });
        });

        User::computed('orders', function ($user) {
            return Order::query()->where('customer', $user->getKey())->orderByDesc('date')->pluck('id')->all();
        });

        Blueprint::addNamespace('simple-commerce', __DIR__.'/../resources/blueprints');

        if (! Blueprint::find('simple-commerce::order')) {
            Blueprint::make('order')->setNamespace('simple-commerce')->save();
        }

        MultisiteCommand::hook('after', function ($payload, $next) {
            Config::set('statamic.system.multisite', false);

            $this->components->task(
                description: 'Updating carts',
                task: function () {
                    $base = \Statamic\Facades\Stache::store('carts')->directory();

                    File::makeDirectory("{$base}/{$this->siteHandle}");

                    File::getFiles($base)->each(function ($file) use ($base) {
                        $filename = pathinfo($file, PATHINFO_BASENAME);
                        File::move($file, "{$base}/{$this->siteHandle}/{$filename}");
                    });
                }
            );

            $this->components->task(
                description: 'Updating orders',
                task: function () {
                    $base = \Statamic\Facades\Stache::store('orders')->directory();

                    File::makeDirectory("{$base}/{$this->siteHandle}");

                    File::getFiles($base)->each(function ($file) use ($base) {
                        $filename = pathinfo($file, PATHINFO_BASENAME);
                        File::move($file, "{$base}/{$this->siteHandle}/{$filename}");
                    });
                }
            );

            Config::set('statamic.system.multisite', true);

            return $next($payload);
        });
    }
}
