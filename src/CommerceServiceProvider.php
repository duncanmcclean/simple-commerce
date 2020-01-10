<?php

namespace Damcclean\Commerce;

use Damcclean\Commerce\Events\AddedToCart;
use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Events\CouponUsed;
use Damcclean\Commerce\Events\NewCustomerCreated;
use Damcclean\Commerce\Events\OrderStatusUpdated;
use Damcclean\Commerce\Events\ProductOutOfStock;
use Damcclean\Commerce\Events\ProductStockRunningLow;
use Damcclean\Commerce\Events\ReturnCustomer;
use Damcclean\Commerce\Fieldtypes\CountryFieldtype;
use Damcclean\Commerce\Fieldtypes\CurrencyFieldtype;
use Damcclean\Commerce\Fieldtypes\CustomerOrdersFieldtype;
use Damcclean\Commerce\Fieldtypes\MoneyFieldtype;
use Damcclean\Commerce\Fieldtypes\OrderStatusFieldtype;
use Damcclean\Commerce\Fieldtypes\ProductFieldtype;
use Damcclean\Commerce\Fieldtypes\ProductCategoryFieldtype;
use Damcclean\Commerce\Listeners\SendOrderStatusUpdatedNotification;
use Damcclean\Commerce\Listeners\SendOrderSuccessfulNotification;
use Damcclean\Commerce\Models\Customer;
use Damcclean\Commerce\Models\Order;
use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\ProductCategory;
use Damcclean\Commerce\Policies\CustomerPolicy;
use Damcclean\Commerce\Policies\OrderPolicy;
use Damcclean\Commerce\Policies\ProductCategoryPolicy;
use Damcclean\Commerce\Policies\ProductPolicy;
use Damcclean\Commerce\Tags\CartTags;
use Damcclean\Commerce\Tags\CommerceTags;
use Damcclean\Commerce\Widgets\NewCustomersWidget;
use Damcclean\Commerce\Widgets\RecentOrdersWidget;
use Illuminate\Support\Facades\Gate;
use Statamic\Facades\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Damcclean\Commerce\Fieldtypes\CustomerFieldtype;

class CommerceServiceProvider extends AddonServiceProvider
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
        ProductOutOfStock::class => [],
        ProductStockRunningLow::class => [],
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

        Statamic::provideToScript([
            'commerceCurrencyCode' => config('commerce.currency.code'),
            'commerceCurrencySymbol' => config('commerce.currency.symbol'),
        ]);

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
