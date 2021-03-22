<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Statamic\Events\EntryBlueprintFound;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $config = false;
    protected $translations = false;

    protected $commands = [
        Console\Commands\CartCleanupCommand::class,
        Console\Commands\InfoCommand::class,
        Console\Commands\MakeGateway::class,
        Console\Commands\MakeShippingMethod::class,
        Console\Commands\InstallCommand::class,
        Console\Commands\UpgradeCommand::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\MoneyFieldtype::class,
        Fieldtypes\ProductVariantFieldtype::class,
        Fieldtypes\ProductVariantsFieldtype::class,
    ];

    protected $listen = [
        EntryBlueprintFound::class  => [
            Listeners\EnforceBlueprintFields::class,
        ],
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp'      => __DIR__.'/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
    ];

    protected $tags = [
        Tags\SimpleCommerceTag::class,
    ];

    protected $widgets = [
        Widgets\SalesWidget::class,
    ];

    public function boot()
    {
        parent::boot();

        Statamic::booted(function () {
            $this
                ->bootVendorAssets()
                ->bindContracts()
                ->bootCartDrivers();
        });

        SimpleCommerce::bootGateways();
        Actions\RefundAction::register();
    }

    protected function bootVendorAssets()
    {
        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/simple-commerce'),
        ], 'simple-commerce');

        $this->publishes([
            __DIR__.'/../config/simple-commerce.php' => config_path('simple-commerce.php'),
        ], 'simple-commerce-config');

        $this->publishes([
            __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
        ], 'simple-commerce-blueprints');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/simple-commerce'),
        ], 'simple-commerce-translations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/simple-commerce'),
        ], 'simple-commerce-views');

        if (app()->environment() !== 'testing') {
            $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'simple-commerce');
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'simple-commerce');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'simple-commerce');

        return $this;
    }

    protected function bindContracts()
    {
        collect([
            Contracts\Calculator::class         => Orders\Calculator::class,
            // Contracts\Order::class              => Orders\Order::class,
            Contracts\Order::class              => Orders\EloquentOrder::class,
            Contracts\Coupon::class             => Coupons\Coupon::class,
            Contracts\Currency::class           => Support\Currency::class,
            Contracts\Customer::class           => Customers\Customer::class,
            Contracts\GatewayManager::class     => Gateways\Manager::class,
            Contracts\Product::class            => Products\Product::class,
            Contracts\ShippingManager::class    => Shipping\ShippingManager::class,
        ])->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

        return $this;
    }

    protected function bootCartDrivers()
    {
        if (! $this->app->bound(Contracts\CartDriver::class)) {
            $this->app->bind(Contracts\CartDriver::class, config('simple-commerce.cart.driver'));
        }

        return $this;
    }
}
