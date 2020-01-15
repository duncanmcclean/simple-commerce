<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Console\Commands\CartDeletionCommand;
use DoubleThreeDigital\SimpleCommerce\Console\Commands\SeederCommand;
use DoubleThreeDigital\SimpleCommerce\Events\AddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Events\CouponUsed;
use DoubleThreeDigital\SimpleCommerce\Events\NewCustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantStockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\ReturnCustomer;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CurrencyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerOrdersFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\OrderStatusFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductCategoryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderStatusUpdatedNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderSuccessfulNotification;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Policies\CustomerPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\OrderPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\ProductCategoryPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\ProductPolicy;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use DoubleThreeDigital\SimpleCommerce\Tags\CommerceTags;
use DoubleThreeDigital\SimpleCommerce\Widgets\NewCustomersWidget;
use DoubleThreeDigital\SimpleCommerce\Widgets\RecentOrdersWidget;
use Illuminate\Support\Facades\Gate;
use Statamic\Facades\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerFieldtype;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $tags = [
        CartTags::class,
        CommerceTags::class,
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/cp.js',
    ];

    protected $listen = [
        AddedToCart::class => [],
        CheckoutComplete::class => [
            SendOrderSuccessfulNotification::class,
        ],
        CouponUsed::class => [],
        NewCustomerCreated::class => [],
        OrderStatusUpdated::class => [
            SendOrderStatusUpdatedNotification::class,
        ],
        VariantOutOfStock::class => [],
        VariantStockRunningLow::class => [],
        ReturnCustomer::class => [],
    ];

    protected $widgets = [
        RecentOrdersWidget::class,
        NewCustomersWidget::class,
    ];

    protected $policies = [
        Customer::class => CustomerPolicy::class,
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
    ];

    protected $commands = [
        CartDeletionCommand::class,
        SeederCommand::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/commerce.php' => config_path('commerce.php'),
        ], 'commerce-config');

        $this->publishes([
            __DIR__.'/../resources/views/web' => resource_path('views/vendor/commerce/web'),
        ], 'commerce-views');

        $this->publishes([
            __DIR__.'/../dist/js/web.js' => resource_path('js/web.js'),
        ], 'commerce-scripts');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'commerce-migrations');

        $this->publishes([
            __DIR__.'/../database/seeds' => database_path('seeds'),
        ], 'commerce-seeders');

        $this->publishes([
            __DIR__.'/../resources/blueprints' => resource_path('blueprints')
        ], 'commerce-blueprints');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'commerce');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->booted(function () {
            Statamic::provideToScript([
                'commerceCurrencyCode' => Currency::where('primary', true)->first()->iso,
                'commerceCurrencySymbol' => Currency::where('primary', true)->first()->symbol,
            ]);
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Products')
                ->section('Commerce')
                ->route('products.index')
                ->icon('entries')
                ->children([
                    'Categories' => cp_route('product-categories.index')
                ]);
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Orders')
                ->section('Commerce')
                ->route('orders.index')
                ->icon('list');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Customers')
                ->section('Commerce')
                ->route('customers.index')
                ->icon('user');
        });

//        Nav::extend(function ($nav) {
//            $nav
//                ->create('Coupons')
//                ->section('Commerce')
//                ->route('coupons.index');
//        });

        CountryFieldtype::register();
        CurrencyFieldtype::register();
        CustomerFieldtype::register();
        CustomerOrdersFieldtype::register();
        MoneyFieldtype::register();
        OrderStatusFieldtype::register();
        ProductCategoryFieldtype::register();
        ProductFieldtype::register();

        $this->app->booted(function() {
            //Permission::group('commerce', 'commerce');

            Permission::register('view customers', function ($permission) {
                $permission->children([
                    Permission::make('edit customers')
                        ->label('Edit customers')
                        ->group('Commerce')
                        ->children([
                            Permission::make('create customers')
                                ->label('Create Customers')
                                ->group('Commerce'),
                            Permission::make('delete customers')
                                ->label('Delete Customers')
                                ->group('Commerce'),
                        ])
                ]);
            })->label('View Customers')->group('Commerce');

            Permission::register('view orders', function ($permission) {
                $permission->children([
                    Permission::make('edit orders')
                        ->label('Edit Orders')
                        ->group('Commerce')
                        ->children([
                            Permission::make('create orders')
                                ->label('Create Orders')
                                ->group('Commerce'),
                            Permission::make('refund orders')
                                ->label('Refund Orders')
                                ->group('Commerce'),
                            Permission::make('delete orders')
                                ->label('Delete Orders')
                                ->group('Commerce'),
                        ])
                ]);
            })->label('View Orders')->group('commerce');

            Permission::register('view products', function ($permission) {
                $permission->children([
                    Permission::make('edit products')
                        ->label('Edit Products')
                        ->group('Commerce')
                        ->children([
                            Permission::make('create products')
                                ->label('Create Products')
                                ->group('Commerce'),
                            Permission::make('delete products')
                                ->label('Delete Products')
                                ->group('Commerce'),
                        ])
                ]);
            })->label('View Products')->group('Commerce');

            Permission::register('view product categories', function ($permission) {
                $permission->children([
                    Permission::make('edit product categories')
                        ->label('Edit Product Categories')
                        ->group('Commerce')
                        ->children([
                            Permission::make('create product categories')
                                ->label('Create Product Categories')
                                ->group('Commerce'),
                            Permission::make('delete product categories')
                                ->label('Delete Product Categories')
                                ->group('Commerce'),
                        ])
                ]);
            })->label('View Product Categories')->group('commerce');
        });
    }

    public function register()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/commerce.php', 'commerce');
        }

        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }
}
