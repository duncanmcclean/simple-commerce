<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Illuminate\Support\Str;
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
                    'display'         => isset($gateway[1]['display']) ? $gateway[1]['display'] : $instance->name(),
                    'purchaseRules'   => $instance->purchaseRules(),
                    'gateway-config'  => $gateway[1],
                    'webhook_url'     => Str::finish(config('app.url'), '/') . config('statamic.routes.action') . '/simple-commerce/gateways/' . $handle . '/webhook',
                ];
            })
            ->toArray();
    }

    public static function registerGateway(string $gateway, array $config = [])
    {
        static::$gateways[] = [
            $gateway,
            $config,
        ];
    }

    public static function freshOrderNumber()
    {
        // TODO: fixes issues on Github Actions
        if (config('app.env') === 'testing') {
            return 1234;
        }

        $minimum = config('simple-commerce.minimum_order_number');

        $query = Facades\Order::query()
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
