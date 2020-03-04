<?php

namespace DoubleThreeDigital\SimpleCommerce;

class SimpleCommerce
{
    protected static $gateways = [];

    public static function registerGateway($class)
    {
        static::$gateways[] = $class;

        return new static;
    }

    public static function gateways()
    {
        return collect(static::$gateways)
            ->map(function ($gateway) {
                $instance = new $gateway;

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
