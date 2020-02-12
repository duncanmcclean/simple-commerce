<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Console\Commands\CartDeletionCommand;
use DoubleThreeDigital\SimpleCommerce\Console\Commands\SeederCommand;
use DoubleThreeDigital\SimpleCommerce\Events\AddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Events\NewCustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\ReturnCustomer;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantStockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CurrencyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerOrdersFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\OrderStatusFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\OrderStatusSettingsFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductCategoryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ShippingZoneSettingsFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\StateFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\TaxRateSettingsFieldtype;
use DoubleThreeDigital\SimpleCommerce\Http\Middleware\AccessSettings;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderRefundedNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderStatusUpdatedNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderSuccessfulNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendVariantOutOfStockNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendVariantStockRunningLowNotification;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Modifiers\PriceModifier;
use DoubleThreeDigital\SimpleCommerce\Policies\CustomerPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\OrderPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\ProductCategoryPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\ProductPolicy;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use DoubleThreeDigital\SimpleCommerce\Tags\CommerceTags;
use DoubleThreeDigital\SimpleCommerce\Widgets\NewCustomersWidget;
use DoubleThreeDigital\SimpleCommerce\Widgets\RecentOrdersWidget;
use Statamic\Facades\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        AddedToCart::class => [],
        CheckoutComplete::class => [
            SendOrderSuccessfulNotification::class,
        ],
        NewCustomerCreated::class => [],
        OrderRefunded::class => [
            SendOrderRefundedNotification::class,
        ],
        OrderStatusUpdated::class => [
            SendOrderStatusUpdatedNotification::class,
        ],
        ReturnCustomer::class => [],
        VariantOutOfStock::class => [
            SendVariantOutOfStockNotification::class,
        ],
        VariantStockRunningLow::class => [
            SendVariantStockRunningLowNotification::class,
        ],
    ];

    protected $tags = [
        CartTags::class,
        CommerceTags::class,
    ];

    protected $fieldtypes = [
        CountryFieldtype::class,
        CurrencyFieldtype::class,
        CustomerFieldtype::class,
        CustomerOrdersFieldtype::class,
        MoneyFieldtype::class,
        OrderStatusFieldtype::class,
        OrderStatusSettingsFieldtype::class,
        ProductCategoryFieldtype::class,
        ProductFieldtype::class,
        ShippingZoneSettingsFieldtype::class,
        StateFieldtype::class,
        TaxRateSettingsFieldtype::class,
    ];

    protected $modifiers = [
        PriceModifier::class,
    ];

    protected $widgets = [
        NewCustomersWidget::class,
        RecentOrdersWidget::class,
    ];

    protected $policies = [
        Customer::class => CustomerPolicy::class,
        Order::class => OrderPolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
        Product::class => ProductPolicy::class,
    ];

    protected $commands = [
        CartDeletionCommand::class,
        SeederCommand::class,
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/cp.js',
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
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
            __DIR__.'/../resources/blueprints/simple-commerce' => resource_path('blueprints'),
        ], 'commerce-blueprints');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'commerce');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Nav::extend(function ($nav) {
            $nav
                ->create('Products')
                ->section('Simple Commerce')
                ->route('products.index')
                ->icon('entries')
                ->children([
                    'Categories' => cp_route('product-categories.index'),
                ]);
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Orders')
                ->section('Simple Commerce')
                ->route('orders.index')
                ->icon('list');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Customers')
                ->section('Simple Commerce')
                ->route('customers.index')
                ->icon('user');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Settings')
                ->section('Simple Commerce')
                ->route('settings.edit')
                ->icon('settings-horizontal');
        });

        $this->app->booted(function () {
            Permission::group('simple-commerce', 'Simple Commerce', function () {
                Permission::register('edit simple commerce settings')
                    ->label('Edit Simple Commerce Settings');

                Permission::register('view customers', function ($permission) {
                    $permission->children([
                        Permission::make('edit customers')
                            ->label('Edit customers')
                            ->children([
                                Permission::make('create customers')
                                    ->label('Create Customers'),
                                Permission::make('delete customers')
                                    ->label('Delete Customers'),
                            ]),
                    ]);
                })->label('View Customers');

                Permission::register('view orders', function ($permission) {
                    $permission->children([
                        Permission::make('edit orders')
                            ->label('Edit Orders')
                            ->children([
                                Permission::make('refund orders')
                                    ->label('Refund Orders'),
                                Permission::make('delete orders')
                                    ->label('Delete Orders'),
                            ]),
                    ]);
                })->label('View Orders');

                Permission::register('view products', function ($permission) {
                    $permission->children([
                        Permission::make('edit products')
                            ->label('Edit Products')
                            ->children([
                                Permission::make('create products')
                                    ->label('Create Products'),
                                Permission::make('delete products')
                                    ->label('Delete Products'),
                            ]),
                    ]);
                })->label('View Products');

                Permission::register('view product categories', function ($permission) {
                    $permission->children([
                        Permission::make('edit product categories')
                            ->label('Edit Product Categories')
                            ->children([
                                Permission::make('create product categories')
                                    ->label('Create Product Categories'),
                                Permission::make('delete product categories')
                                    ->label('Delete Product Categories'),
                            ]),
                    ]);
                })->label('View Product Categories');
            });
        });
    }

    public function register()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/commerce.php', 'commerce');
        }
    }
}
