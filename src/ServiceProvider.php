<?php

namespace Damcclean\Commerce;

use Damcclean\Commerce\Console\Commands\SetupCommerceCommand;
use Damcclean\Commerce\Contracts\CouponRepository;
use Damcclean\Commerce\Contracts\ProductRepository;
use Damcclean\Commerce\Facades\Coupon;
use Damcclean\Commerce\Facades\Product;
use Damcclean\Commerce\Stache\Repositories\FileCouponRepository;
use Damcclean\Commerce\Stache\Repositories\FileProductRepository;
use Damcclean\Commerce\Tags\CartTags;
use Damcclean\Commerce\Tags\CommerceTags;
use Damcclean\Commerce\Tags\ProductTags;
use Statamic\Facades\Nav;
use Statamic\Providers\AddonServiceProvider;

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
        ProductTags::class,
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/cp.js'
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
                SetupCommerceCommand::class
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
        $this->app->bind(CouponRepository::class, FileCouponRepository::class);

        $this->app->bind('product', Product::class);
        $this->app->bind('coupon', Coupon::class);
    }
}
