<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Facades\Statamic\Console\Processes\Composer;

class SimpleCommerce
{
    protected static $gateways = [];

    public static function getVersion()
    {
        return Composer::installedVersion('doublethreedigital/simple-commerce');
    }

    public static function bootGateways()
    {
        return app()->booted(function () {
            foreach (config('simple-commerce.gateways') as $class => $config) {
                static::$gateways[] = [
                    $class,
                    $config,
                ];
            }

            return new static;
        });
    }

    public static function gateways()
    {
        return collect(static::$gateways)
            ->map(function ($gateway) {
                $instance = new $gateway[0];

                return [
                    'name' => $instance->name(),
                    'class' => $gateway[0],
                    'rules' => $instance->rules(),
                    'payment_form' => $instance->paymentForm()->render(),
                    'config' => $gateway[1],
                ];
            })
            ->toArray();
    }
}
