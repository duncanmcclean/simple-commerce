<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Illuminate\Support\Str;
use Statamic\Facades\Collection;
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
                    'handle'          => $handle = Str::camel($instance->name()),
                    'class'           => $gateway[0],
                    'formatted_class' => addslashes($gateway[0]),
                    'purchaseRules'   => $instance->purchaseRules(),
                    'gateway-config'  => $gateway[1],
                    'webhook_url'     => Statamic::booted(function () use ($handle) {
                        return route('statamic.simple-commerce.gateways.webhook', ['gateway' => $handle]);
                    }),
                ];
            })
            ->toArray();
    }

    public static function registerGateway(string $gateway, array $config = [])
    {
        static::$gateways[] = [
            $gateway,
            $config
        ];
    }

    public static function freshOrderNumber()
    {
        $minimum = config('simple-commerce.minimum_order_number');

        $query = Collection::find(config('simple-commerce.collections.orders'))
            ->queryEntries()
            ->orderBy('title', 'asc')
            ->where('title', '!=', null)
            ->get()
            ->map(function ($order) {
                $order->title = str_replace('Order ', '', $order->title);
                $order->title = str_replace('#', '', $order->title);

                return $order->title;
            })
            ->last();

        if (!$query) {
            return $minimum + 1;
        }

        return ((int) $query) + 1;
    }
}
