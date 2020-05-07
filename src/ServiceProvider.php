<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Console\Commands\InstallCommand;
use DoubleThreeDigital\SimpleCommerce\Console\Commands\VersionCommand;
use DoubleThreeDigital\SimpleCommerce\Events\AttributeUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Events\ProductCategoryUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\ProductUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\VariantLowStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantUpdated;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CurrencyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerOrdersFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\LineItemsFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\OrderStatusFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductCategoryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\StateFieldtype;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\TaxRateFieldtype;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderRefundedNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderStatusUpdatedNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendOrderSuccessfulNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendVariantOutOfStockNotification;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendVariantStockRunningLowNotification;
use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Modifiers\PriceModifier;
use DoubleThreeDigital\SimpleCommerce\Policies\CouponPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\OrderPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\ProductCategoryPolicy;
use DoubleThreeDigital\SimpleCommerce\Policies\ProductPolicy;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTag;
use DoubleThreeDigital\SimpleCommerce\Tags\SimpleCommerceTag;
use DoubleThreeDigital\SimpleCommerce\Widgets\RecentOrdersWidget;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        AttributeUpdated::class => [],
        CouponRedeemed::class => [],
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
        LineItemsFieldtype::class,
        OrderStatusFieldtype::class,
        ProductCategoryFieldtype::class,
        ProductFieldtype::class,
        StateFieldtype::class,
        TaxRateFieldtype::class,
    ];

    protected $modifiers = [
        PriceModifier::class,
    ];

    protected $widgets = [
        RecentOrdersWidget::class,
    ];

    protected $policies = [
        Coupon::class => CouponPolicy::class,
        Order::class => OrderPolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
        Product::class => ProductPolicy::class,
    ];

    protected $commands = [
        InstallCommand::class,
        VersionCommand::class,
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function boot()
    {
        parent::boot();

        $this->publishVendorStuff();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'simple-commerce');

        $this->app->booted(function () {
            $this->navigation();
            $this->permissions();
        });

        if (config('statamic.api.enabled')) {
            Route::middleware(config('statamic.api.middleware'))
                ->name('simple-commerce.api.')
                ->prefix(config('statamic.api.route').'/simple-commerce/')
                ->namespace('DoubleThreeDigital\SimpleCommerce\Http\Controllers\API')
                ->group(__DIR__.'/../routes/api.php');
        }

        SimpleCommerce::bootGateways();
    }

    public function register()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'simple-commerce');
        }

        $this->app->bind('Cart', \DoubleThreeDigital\SimpleCommerce\Support\Cart::class);
        $this->app->bind('Currency', \DoubleThreeDigital\SimpleCommerce\Support\Currency::class);
        $this->app->bind('FormBuilder', \DoubleThreeDigital\SimpleCommerce\Support\FormBuilder::class);
    }

    protected function publishVendorStuff()
    {
        $this->publishes([
            __DIR__.'/../config/simple-commerce.php' => config_path('simple-commerce.php'),
        ], 'simple-commerce-config');

        $this->publishes([
            __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
        ], 'simple-commerce-blueprints');

        $this->publishes([
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets'),
        ], 'simple-commerce-fieldsets');

        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/simple-commerce'),
        ], 'simple-commerce-assets');
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
                ->icon('list')
                ->children(array_merge([
                    $nav
                        ->item('All Orders')
                        ->route('orders.index')
                        ->can('view orders'),
                    $nav
                        ->item('Carts')
                        ->url(cp_route('orders.index').'?view-carts=true')
                        ->can('view orders'),
                ], OrderStatus::all()->map(function ($status) use ($nav) {
                    return $nav
                        ->item($status->name)
                        ->url(cp_route('orders.index').'?status='.$status->slug)
                        ->can('view orders');
                })->toArray()));
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Coupons')
                ->section('Simple Commerce')
                ->route('coupons.index')
                ->can('view coupons')
                ->icon('tags');
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
                        ->item('Shipping')
                        ->route('settings.shipping.index')
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

            Permission::register('view coupons', function ($permission) {
                $permission->children([
                    Permission::make('edit coupons')
                        ->label('Edit Coupons')
                        ->children([
                            Permission::make('create coupons')
                                ->label('Create Coupons'),
                            Permission::make('delete coupons')
                                ->label('Delete Coupons'),
                        ]),
                ]);
            })->label('View Coupons');
        });
    }
}
