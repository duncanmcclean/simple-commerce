<?php

namespace Damcclean\Commerce;

use Damcclean\Commerce\Contracts\CouponRepository;
use Damcclean\Commerce\Contracts\CustomerRepository;
use Damcclean\Commerce\Contracts\OrderRepository;
use Damcclean\Commerce\Contracts\ProductRepository;
use Damcclean\Commerce\Events\AddedToCart;
use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Events\CouponUsed;
use Damcclean\Commerce\Events\NewCustomerCreated;
use Damcclean\Commerce\Events\OrderStatusUpdated;
use Damcclean\Commerce\Events\ProductOutOfStock;
use Damcclean\Commerce\Events\ProductStockRunningLow;
use Damcclean\Commerce\Events\ReturnCustomer;
use Damcclean\Commerce\Facades\Coupon;
use Damcclean\Commerce\Facades\Customer;
use Damcclean\Commerce\Facades\Order;
use Damcclean\Commerce\Facades\Product;
use Damcclean\Commerce\Fieldtypes\Money;
use Damcclean\Commerce\Fieldtypes\Product as ProductFieldtype;
use Damcclean\Commerce\Listeners\SendOrderStatusUpdatedNotification;
use Damcclean\Commerce\Listeners\SendOrderSuccessfulNotification;
use Damcclean\Commerce\Tags\CartTags;
use Damcclean\Commerce\Tags\CommerceTags;
use Damcclean\Commerce\Tags\ProductTags;
use Statamic\Facades\Nav;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Damcclean\Commerce\Fieldtypes\Customer as CustomerFieldtype;

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
        ProductTags::class,
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
            __DIR__.'/../resources/blueprints' => resource_path('blueprints')
        ], 'commerce-blueprints');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'commerce');

        Statamic::provideToScript([
            'commerceCurrencyCode' => config('commerce.currency.code'),
            'commerceCurrencySymbol' => config('commerce.currency.symbol'),
        ]);

        Nav::extend(function ($nav) {
            $nav
                ->create('Dashboard')
                ->section('Commerce')
                ->route('commerce.dashboard');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Products')
                ->section('Commerce')
                ->route('products.index');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Orders')
                ->section('Commerce')
                ->route('orders.index');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Customers')
                ->section('Commerce')
                ->route('customers.index');
        });

        Nav::extend(function ($nav) {
            $nav
                ->create('Coupons')
                ->section('Commerce')
                ->route('coupons.index');
        });

        Money::register();
        ProductFieldtype::register();
        CustomerFieldtype::register();
    }

    public function register()
    {
        // Coupons
        $this->app->bind(CouponRepository::class, config('commerce.storage.coupons.repository'));
        $this->app->bind('coupon', Coupon::class);

        // Customers
        $this->app->bind(CustomerRepository::class, config('commerce.storage.customers.repository'));
        $this->app->bind('customer', Customer::class);

        // Orders
        $this->app->bind(OrderRepository::class, config('commerce.storage.orders.repository'));
        $this->app->bind('order', Order::class);

        // Products
        $this->app->bind(ProductRepository::class, config('commerce.storage.products.repository'));
        $this->app->bind('product', Product::class);

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/commerce.php', 'commerce');
        }
    }
}
