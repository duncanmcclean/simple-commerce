<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Console\Commands\CartDeletionCommand;
use DoubleThreeDigital\SimpleCommerce\Console\Commands\SeederCommand;
use DoubleThreeDigital\SimpleCommerce\Events\AddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\AttributeUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\CartCreated;
use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\NewCustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\OrderFailed;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Events\ProductCategoryUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\ProductUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\RemovedFromCart;
use DoubleThreeDigital\SimpleCommerce\Events\ReturnCustomer;
use DoubleThreeDigital\SimpleCommerce\Events\ShippingAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\TaxAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\VariantLowStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantStockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\VariantUpdated;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CurrencyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerOrdersFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\OrderItemsFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\OrderStatusFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductCategoryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\StateFieldtype;
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
        AttributeUpdated::class => [],
        CartCreated::class => [],
        CustomerCreated::class => [],
        CustomerUpdated::class => [],
        OrderFailed::class => [],
        OrderPaid::class => [],
        OrderRefunded::class => [
            SendOrderRefundedNotification::class,
        ],
        OrderStatusUpdated::class => [
            SendOrderStatusUpdatedNotification::class,
        ],
        OrderSuccessful::class => [
            SendOrderSuccessfulNotification::class,
        ],
        ProductCategoryUpdated::class => [],
        ProductUpdated::class => [],
        RemovedFromCart::class => [],
        ShippingAddedToCart::class => [],
        TaxAddedToCart::class => [],
        VariantLowStock::class => [],
        VariantOutOfStock::class => [
            SendVariantOutOfStockNotification::class,
        ],
        VariantUpdated::class => [
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
        OrderItemsFieldtype::class,
        OrderStatusFieldtype::class,
        ProductCategoryFieldtype::class,
        ProductFieldtype::class,
        StateFieldtype::class,
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
            __DIR__.'/../config/simple-commerce.php' => config_path('simple-commerce.php'),
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
            __DIR__.'/../resources/blueprints' => resource_path('blueprints/simple-commerce'),
        ], 'commerce-blueprints');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'simple-commerce');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Nav::extend(function ($nav) {
            $nav
                ->create('Products')
                ->section('Simple Commerce')
                ->route('products.index')
                ->can('view products')
                ->icon('entries')
                ->children([
                    $nav
                        ->item('Categories')
                        ->route('product-categories.index')
                        ->can('view product categories'),
                ]);
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Orders')
                ->section('Simple Commerce')
                ->route('orders.index')
                ->can('view orders')
                ->icon('list');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Customers')
                ->section('Simple Commerce')
                ->route('customers.index')
                ->can('view customers')
                ->icon('user');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Settings')
                ->section('Simple Commerce')
                ->route('settings.index')
                ->can('view simple commerce settings')
                ->icon('settings-horizontal')
                ->children([
                    $nav
                        ->item('Order Statuses')
                        ->route('settings.order-statuses.index')
                        ->can('view simple commerce settings'),
                    $nav
                        ->item('Shipping Zones')
                        ->route('settings.shipping-zones.index')
                        ->can('view simple commerce settings'),
                    $nav
                        ->item('Tax Rates')
                        ->route('settings.tax-rates.index')
                        ->can('view simple commerce settings'),
                ]);
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

        SimpleCommerce::bootGateways();
    }

    public function register()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'commerce');
        }
    }
}
