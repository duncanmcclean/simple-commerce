<?php

namespace Damcclean\Commerce;

use Statamic\Facades\Nav;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php'
    ];

    public function boot()
    {
        parent::boot();

        $this
            ->publishes([
                __DIR__.'/../config/commerce.php' => config_path('commerce.php'),
            ]);

        $this
            ->loadViewsFrom(__DIR__.'/../resources/views', 'commerce');

        Nav::extend(function ($nav) {
            $nav->create('Products')
                ->section('Commerce')
                ->route('products.index');
        });

        Nav::extend(function ($nav) {
            $nav->create('Coupons')
                ->section('Commerce')
                ->route('coupons.index');
        });

        Statamic::script('commerce', 'cp.js');
    }
}
