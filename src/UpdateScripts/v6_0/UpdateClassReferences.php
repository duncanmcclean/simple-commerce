<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v6_0;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Statamic\UpdateScripts\UpdateScript;

class UpdateClassReferences extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        Order::query()
            ->where('gateway', '!=', null)
            ->chunk(50, function ($orders) {
                $orders->each(function ($order) {
                    // When the gateway reference is still a class, change it to the handle.
                    if ($order->gateway() && class_exists($order->gateway()['use'])) {
                        $order->gateway(array_merge($order->gateway(), [
                            'use' => $order->gateway()['use']::handle(),
                        ]));

                        $order->save();
                    }

                    // When the shipping method reference is still a class, change it to the handle.
                    if ($order->has('shipping_method') && class_exists($order->get('shipping_method'))) {
                        $order->set('shipping_method', $order->get('shipping_method')::handle())->saveQuietly();
                    }
                });
            });
    }
}
