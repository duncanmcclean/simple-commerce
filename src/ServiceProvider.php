<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        Fieldtypes\MoneyFieldtype::class,
    ];

    protected $listen = [
        Events\CartCompleted::class => [],
        Events\CartSaved::class => [],
        Events\CartUpdated::class => [],
        Events\CustomerAddedToCart::class => [],
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
        Widgets\RevenueWidget::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
        ], 'simple-commerce-blueprints');

        $this->publishes([
            __DIR__.'/../config/simple-commerce.php' => config_path('simple-commerce.php'),
        ], 'simple-commerce-config');

        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/simple-commerce'),
        ], 'simple-commerce-assets');

        $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'simple-commerce');

        Statamic::booted(function () {
            $this
                ->contentSetup()
                ->bindRepositories();
        });

        SimpleCommerce::bootGateways();
    }

    public function contentSetup()
    {
        if (! Collection::handleExists('products')) {
            Collection::make('products')
                ->title('Products')
                ->pastDateBehavior('public')
                ->futureDateBehavior('private')
                ->entryBlueprints(['product'])
                ->sites(['default'])
                ->routes('/products/{slug}')
                ->save();
        }

        if (! Collection::handleExists('orders')) {
            Collection::make('orders')
                ->title('Orders')
                ->entryBlueprints(['order'])
                ->sites(['default'])
                ->save();
        }

        if (! Collection::handleExists('coupons')) {
            Collection::make('coupons')
                ->title('Coupons')
                ->entryBlueprints(['coupon'])
                ->sites(['default'])
                ->save();
        }

        if (! Taxonomy::handleExists('product_categories')) {
            Taxonomy::make('product_categories')
                ->title('Product Categories')
                ->save();
        }

        if (! Taxonomy::handleExists(('order_statuses'))) {
            Taxonomy::make('order_statuses')
                ->title('Order Statuses')
                ->save();
        }

        return $this;
    }

    public function bindRepositories()
    {
        $this->app->bind('Cart', Repositories\CartRepository::class);
        $this->app->bind('Currency', Repositories\CurrencyRepository::class);

        return $this;
    }
}