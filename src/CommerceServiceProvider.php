<?php

namespace Damcclean\Commerce;

use Damcclean\Commerce\Console\Commands\SetupCommerceCommand;
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
use Damcclean\Commerce\Listeners\SendOrderSuccessfulNotification;
use Damcclean\Commerce\Stache\Repositories\FileCouponRepository;
use Damcclean\Commerce\Stache\Repositories\FileCustomerRepository;
use Damcclean\Commerce\Stache\Repositories\FileOrderRepository;
use Damcclean\Commerce\Stache\Repositories\FileProductRepository;
use Damcclean\Commerce\Tags\CartTags;
use Damcclean\Commerce\Tags\CommerceTags;
use Damcclean\Commerce\Tags\ProductTags;
use Statamic\Facades\Nav;
use Statamic\Providers\AddonServiceProvider;

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
        OrderStatusUpdated::class => [],
        ProductOutOfStock::class => [],
        ProductStockRunningLow::class => [],
        ReturnCustomer::class => [],
    ];

    public function boot()
    {
        parent::boot();

        $this
            ->publishes([
                __DIR__.'/../config/commerce.php' => config_path('commerce.php'),
            ], 'config');

        $this
            ->loadViewsFrom(__DIR__.'/../resources/views', 'commerce');

        $this
            ->commands([
                SetupCommerceCommand::class,
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
    }

    public function register()
    {
        $this->app->bind(ProductRepository::class, FileProductRepository::class);
        $this->app->bind(CustomerRepository::class, FileCustomerRepository::class);
        $this->app->bind(OrderRepository::class, FileOrderRepository::class);
        $this->app->bind(CouponRepository::class, FileCouponRepository::class);

        $this->app->bind('product', Product::class);
        $this->app->bind('customer', Customer::class);
        $this->app->bind('order', Order::class);
        $this->app->bind('coupon', Coupon::class);
    }
}
