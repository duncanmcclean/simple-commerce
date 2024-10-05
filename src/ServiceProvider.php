<?php

namespace DuncanMcClean\SimpleCommerce;

use DuncanMcClean\SimpleCommerce\Coupons\CouponStore;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Stache\Query\CartQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Query\CouponQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Query\OrderQueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache\Repositories\CouponRepository;
use DuncanMcClean\SimpleCommerce\Stache\Stores\CartsStore;
use DuncanMcClean\SimpleCommerce\Stache\Stores\CouponsStore;
use DuncanMcClean\SimpleCommerce\Stache\Stores\OrdersStore;
use Statamic\Facades\Blueprint;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\Delete::class,
    ];

    protected $commands = [
        Console\Commands\MigrateOrders::class,
        Console\Commands\PurgeAbandonedCarts::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\CouponAmountFieldtype::class,
        Fieldtypes\CouponCodeFieldtype::class,
        Fieldtypes\CouponFieldtype::class,
        Fieldtypes\CustomerFieldtype::class,
        Fieldtypes\LineItemsFieldtype::class,
        Fieldtypes\MoneyFieldtype::class,
        Fieldtypes\OrderReceiptFieldtype::class,
        Fieldtypes\OrdersFieldtype::class,
        Fieldtypes\OrderStatusFieldtype::class,
        Fieldtypes\PaymentDetailsFieldtype::class,
        Fieldtypes\ProductVariantsFieldtype::class,
        Fieldtypes\ShippingDetailsFieldtype::class,
    ];

    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            Listeners\AssignUserToCart::class,
        ],
        \Statamic\Events\UserBlueprintFound::class => [
            Listeners\EnsureUserFields::class,
        ],
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scopes = [
        Query\Scopes\Filters\CouponType::class,
        Query\Scopes\Filters\OrderStatus::class,
    ];

    protected $tags = [
        Tags\SimpleCommerceTag::class,
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

    public function bootAddon()
    {
        $this->app['stache']->registerStores([
            (new CartsStore)->directory(config('simple-commerce.carts.directory')),
            (new CouponsStore)->directory(config('simple-commerce.coupons.directory')),
            (new OrdersStore)->directory(config('simple-commerce.orders.directory')),
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
        ])->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

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
                    ]);
                });
            });
        });

        User::computed('orders', function ($user) {
            return Order::query()->where('customer', $user->getKey())->pluck('id')->all();
        });

        Blueprint::addNamespace('simple-commerce', __DIR__.'/../resources/blueprints');

        if (! Blueprint::find("simple-commerce::order")) {
            Blueprint::make('order')->setNamespace('simple-commerce')->save();
        }
    }
}
