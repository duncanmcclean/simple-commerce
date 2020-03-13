<?php

namespace DoubleThreeDigital\SimpleCommerce;

class SimpleCommerce
{
    protected static $gateways = [];

    public static function bootGateways()
    {
        if (app()->booted()) {
            foreach (config('simple-commerce.gateways') as $class => $config) {
                static::$gateways[] = $class;
            }

            return new static;
        }
    }

    public static function gateways()
    {
        return collect(static::$gateways)
            ->map(function ($gateway) {
                $instance = new $gateway;

                // TODO: add the config in here too (the array after the gateway class)
                return [
                    'name' => $instance->name(),
                    'class' => $gateway,
                    'rules' => $instance->rules(),
                    'payment_form' => $instance->paymentForm()->render(),
                ];
            })
            ->toArray();
    }
}
