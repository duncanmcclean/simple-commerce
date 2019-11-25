<?php

namespace Damcclean\Commerce;

use Damcclean\Commerce\Console\Commands\SetupCommerceCommand;
use Damcclean\Commerce\Tags\CartTags;
use Damcclean\Commerce\Tags\CommerceTags;
use Damcclean\Commerce\Tags\ProductTags;
use Statamic\Facades\Nav;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

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

        Statamic::script('commerce', 'cp.js');
    }
}
