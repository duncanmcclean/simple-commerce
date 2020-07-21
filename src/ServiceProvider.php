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

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/simple-commerce.php' => config_path('simple-commerce.php'),
        ], 'simple-commerce-config');

        $this->publishes([
            __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
        ], 'simple-commerce-blueprints');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/simple-commerce'),
        ], 'simple-commerce-translators');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/simple-commerce'),
        ], 'simple-commerce-views');

        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/simple-commerce'),
        ], 'simple-commerce-assets');

        $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'simple-commerce');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'simple-commerce');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'simple-commerce');

        Statamic::booted(function () {
            $this
                ->contentSetup()
                ->bindRepositories();
        });

        SimpleCommerce::bootGateways();
    }

    public function contentSetup()
    {
        if (! Collection::handleExists(config('simple-commerce.collections.products'))) {
            Collection::make(config('simple-commerce.collections.products'))
                ->title(__('simple-commerce::messages.default_collections.products'))
                ->pastDateBehavior('public')
                ->futureDateBehavior('private')
                ->entryBlueprints(['product'])
                ->sites(['default'])
                ->routes('/products/{slug}')
                ->save();
        }

        if (! Collection::handleExists(config('simple-commerce.collections.orders'))) {
            Collection::make(config('simple-commerce.collections.orders'))
                ->title(__('simple-commerce::messages.default_collections.orders'))
                ->entryBlueprints(['order'])
                ->sites(['default'])
                ->save();
        }

        if (! Collection::handleExists(config('simple-commerce.collections.coupons'))) {
            Collection::make(config('simple-commerce.collections.coupons'))
                ->title(__('simple-commerce::messages.default_collections.coupons'))
                ->entryBlueprints(['coupon'])
                ->sites(['default'])
                ->save();
        }

        if (! Taxonomy::handleExists(config('simple-commerce.taxonomies.product_categories'))) {
            Taxonomy::make(config('simple-commerce.taxonomies.product_categories'))
                ->title(__('simple-commerce::messages.default_taxonomies.product_categories'))
                ->save();
        }

        if (! Taxonomy::handleExists(config('simple-commerce.taxonomies.order_statuses'))) {
            Taxonomy::make(config('simple-commerce.taxonomies.order_statuses'))
                ->title(__('simple-commerce::messages.default_taxonomies.order_statuses'))
                ->save();
        }

        return $this;
    }

    public function bindRepositories()
    {
        $this->app->bind('Cart', Repositories\CartRepository::class);
        $this->app->bind('Coupon', Repositories\CouponRepository::class);
        $this->app->bind('Currency', Repositories\CurrencyRepository::class);

        return $this;
    }
}
