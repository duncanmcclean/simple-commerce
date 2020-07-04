<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Statamic\Statamic;

class SimpleCommerce
{
    protected static $gateways = [];

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

    public static function gateways()
    {
        return collect(static::$gateways)
            ->map(function ($gateway) {
                $instance = new $gateway[0]();

                return [
                    'name'            => $instance->name(),
                    'class'           => $gateway[0],
                    'formatted_class' => addslashes($gateway[0]),
                    'purchaseRules'   => $instance->purchaseRules(),
                    'config'          => $gateway[1],
                ];
            })
            ->toArray();
    }
}