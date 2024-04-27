<?php

namespace DuncanMcClean\SimpleCommerce\UpdateScripts\v6_0;

use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\UpdateScripts\UpdateScript;

class UpdateClassReferences extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        $this
            ->updateReferencesToGateways()
            ->updateReferencesToShippingMethods();
    }

    protected function updateReferencesToGateways(): self
    {
        Order::query()->whereNotNull('gateway')->chunk(100, function (Collection $orders) {
            $orders
                ->filter(fn (OrderContract $order) => str_contains(Arr::get($order->gateway, 'use'), '\\'))
                ->each(function (OrderContract $order) {
                    $class = Arr::get($order->gateway, 'use');

                    // Adjust the class name before new'ing it up since the namespace has changed.
                    if (Str::startsWith($class, 'DoubleThreeDigital')) {
                        $class = str_replace('DoubleThreeDigital', 'DuncanMcClean', $class);
                    }

                    $handle = $class::handle();

                    $order->gatewayData(gateway: $handle);
                    $order->save();
                });
        });

        return $this;
    }

    protected function updateReferencesToShippingMethods(): self
    {
        Order::query()->whereNotNull('shipping_method')->chunk(100, function (Collection $orders) {
            $orders
                ->filter(function (OrderContract $order) {
                    $shippingMethod = is_array($order->get('shipping_method'))
                        ? Arr::first($order->get('shipping_method'))
                        : $order->get('shipping_method');

                    return str_contains($shippingMethod, '\\');
                })
                ->each(function (OrderContract $order) {
                    $class = is_array($order->get('shipping_method'))
                        ? Arr::first($order->get('shipping_method'))
                        : $order->get('shipping_method');

                    // Adjust the class name before new'ing it up since the namespace has changed.
                    if (Str::startsWith($class, 'DoubleThreeDigital')) {
                        $class = str_replace('DoubleThreeDigital', 'DuncanMcClean', $class);
                    }

                    $handle = $class::handle();

                    $order->set('shipping_method', $handle);
                    $order->save();
                });
        });

        return $this;
    }
}
