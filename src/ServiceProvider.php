<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Console\Commands\SeederCommand;
use DoubleThreeDigital\SimpleCommerce\Events\AddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\AttributeUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\CartCreated;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Events\ProductCategoryUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\ProductUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\RemovedFromCart;
use DoubleThreeDigital\SimpleCommerce\Events\ShippingAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\TaxAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\VariantLowStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantUpdated;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CurrencyFieldtype;
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
use DoubleThreeDigital\SimpleCommerce\Tags\CartTag;
use DoubleThreeDigital\SimpleCommerce\Tags\SimpleCommerceTag;
use DoubleThreeDigital\SimpleCommerce\Widgets\NewCustomersWidget;
use DoubleThreeDigital\SimpleCommerce\Widgets\RecentOrdersWidget;
use Statamic\Facades\CP\Nav;
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
        VariantLowStock::class => [
            SendVariantStockRunningLowNotification::class,
        ],
        VariantOutOfStock::class => [
            SendVariantOutOfStockNotification::class,
        ],
        VariantUpdated::class => [],
    ];

    protected $tags = [
        CartTag::class,
        SimpleCommerceTag::class,
    ];

    protected $fieldtypes = [
        CountryFieldtype::class,
        CurrencyFieldtype::class,
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
        SeederCommand::class,
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/cp.js',
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function boot()
    {
        parent::boot();

        $this->publishes([__DIR__.'/../config/simple-commerce.php' => config_path('simple-commerce.php')]);
        $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')]);
        $this->publishes([__DIR__.'/../resources/blueprints' => resource_path('blueprints')]);
        $this->publishes([__DIR__.'/../dist/js/cp.js', public_path('vendor/doublethreedigital/simple-commerce/js')]);
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'simple-commerce');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->booted(function () {
            $this->navigation();
            $this->permissions();
        });

        SimpleCommerce::bootGateways();
    }

    public function register()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'simple-commerce');
        }
    }

    protected function navigation()
    {
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
    }

    protected function permissions()
    {
        Permission::group('simple-commerce', 'Simple Commerce', function () {
            Permission::register('edit simple commerce settings')
                ->label('Edit Simple Commerce Settings');

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
    }
}
