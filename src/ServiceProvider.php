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
        Console\Commands\InfoCommand::class,
        Console\Commands\MakeGateway::class,
        Console\Commands\MakeShippingMethod::class,
        Console\Commands\InstallCommand::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\MoneyFieldtype::class,
        Fieldtypes\ProductVariantsFieldtype::class,
    ];

    protected $listen = [
        Events\CartCompleted::class => [
            Listeners\CartCompleted::class,
        ],
        EntryBlueprintFound::class  => [
            Listeners\EnforceBlueprintFields::class,
        ],
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
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
                ->bootRepositories();
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

    protected function bootRepositories()
    {
        collect([
            Contracts\CartRepository::class => Repositories\CartRepository::class,
            Contracts\CouponRepository::class => Repositories\CouponRepository::class,
            Contracts\CurrencyRepository::class => Repositories\CurrencyRepository::class,
            Contracts\CustomerRepository::class => Repositories\CustomerRepository::class,
            Contracts\GatewayRepository::class => Repositories\GatewayRepository::class,
            Contracts\OrderRepository::class => Repositories\OrderRepository::class,
            Contracts\ProductRepository::class => Repositories\ProductRepository::class,
            Contracts\ShippingRepository::class => Repositories\ShippingRepository::class,
        ])->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

        return $this;
    }
}
