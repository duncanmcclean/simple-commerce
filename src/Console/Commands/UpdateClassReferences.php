<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;

class UpdateClassReferences extends Command
{
    use RunsInPlease;

    protected $name = 'sc:update-class-references';

    protected $description = 'Part of the v6 update. Updates references to payment gateway & shipping method classes in orders.';

    public function handle()
    {
        $this->info('Updating class references...');

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

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
