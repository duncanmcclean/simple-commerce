<?php

namespace DuncanMcClean\SimpleCommerce;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Addon;
use Statamic\Facades\Site;
use Statamic\Statamic;

class SimpleCommerce
{
    /** @var array */
    protected static $gateways = [];

    protected static $shippingMethods = [];

    /** @var Contracts\TaxEngine */
    protected static $taxEngine;

    public static $productPriceHook;

    public static $productVariantPriceHook;

    public static function version(): string
    {
        if (app()->environment('testing')) {
            return 'v6.0.0';
        }

        return Addon::get('duncanmcclean/simple-commerce')->version();
    }

    public static function bootGateways()
    {
        return Statamic::booted(function () {
            foreach (config('simple-commerce.gateways') as $class => $config) {
                if ($class) {
                    $class = str_replace('::class', '', $class);

                    static::$gateways[] = [
                        $class,
                        $config,
                    ];
                }
            }

            return new static();
        });
    }

    public static function gateways(): Collection
    {
        return collect(static::$gateways)
            ->map(function ($gateway) {
                $class = $gateway[0];

                /** @var Contracts\Gateway $instance */
                $instance = new $class();

                return [
                    'name' => $instance->name(),
                    'handle' => $class::handle(),
                    'class' => $class,
                    'formatted_class' => addslashes($gateway[0]),
                    'display' => isset($gateway[1]['display']) ? $gateway[1]['display'] : $instance->name(),
                    'checkoutRules' => $instance->checkoutRules(),
                    'config' => $gateway[1],
                ];
            });
    }

    public static function registerGateway(string $gateway, array $config = [])
    {
        static::$gateways[] = [
            $gateway,
            $config,
        ];
    }

    public static function bootTaxEngine()
    {
        return Statamic::booted(function () {
            static::$taxEngine = config('simple-commerce.tax_engine');
        });
    }

    public static function bootShippingMethods()
    {
        return Statamic::booted(function () {
            foreach (config('simple-commerce.sites') as $siteHandle => $value) {
                if (! isset($value['shipping']['methods'])) {
                    continue;
                }

                static::$shippingMethods[$siteHandle] = collect($value['shipping']['methods'])
                    ->map(function ($config, $key) {
                        if (is_string($config)) {
                            $key = $config;
                            $config = [];
                        }

                        $instance = new $key();

                        return [
                            'name' => $instance->name(),
                            'handle' => $key::handle(),
                            'description' => $instance->description(),
                            'class' => $key,
                            'config' => $config,
                        ];
                    })
                    ->toArray();
            }

            return new static();
        });
    }

    public static function setTaxEngine($taxEngine)
    {
        static::$taxEngine = $taxEngine;
    }

    public static function taxEngine(): Contracts\TaxEngine
    {
        return new static::$taxEngine;
    }

    public static function isUsingStandardTaxEngine(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        return static::taxEngine() instanceof Tax\Standard\TaxEngine;
    }

    public static function shippingMethods(?string $site = null)
    {
        if ($site) {
            return collect(static::$shippingMethods[$site] ?? []);
        }

        return collect(static::$shippingMethods[Site::default()->handle()] ?? []);
    }

    public static function registerShippingMethod(string $site, string $shippingMethod, array $config = [])
    {
        $instance = new $shippingMethod();

        static::$shippingMethods[$site][] = [
            'name' => $instance->name(),
            'handle' => $shippingMethod::handle(),
            'description' => $instance->description(),
            'class' => $shippingMethod,
            'config' => $config,
        ];
    }

    public static function orderDriver(): array
    {
        return config('simple-commerce.content.orders');
    }

    public static function productDriver(): array
    {
        return config('simple-commerce.content.products');
    }

    public static function customerDriver(): array
    {
        return config('simple-commerce.content.customers');
    }

    /**
     * This shouldn't be used as a Statamic::svg() replacement. It's only useful for grabbing
     * icons from Simple Commerce's `resources/svgs` directory.
     */
    public static function svg($name)
    {
        if (File::exists(__DIR__.'/../resources/svg/'.$name.'.svg')) {
            return File::get(__DIR__.'/../resources/svg/'.$name.'.svg');
        }

        return Statamic::svg($name);
    }

    public static function productPriceHook(Closure $callback): self
    {
        static::$productPriceHook = $callback;

        return new static;
    }

    public static function productVariantPriceHook(Closure $callback): self
    {
        static::$productVariantPriceHook = $callback;

        return new static;
    }
}
