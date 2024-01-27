<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

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

        // TODO: refactor query
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            Collection::find(SimpleCommerce::orderDriver()['collection'])
                ->queryEntries()
                ->where('gateway', '!=', null)
                ->chunk(50, function ($orders) {
                    $orders->each(function ($entry) {
                        // When the gateway reference is still a class, change it to the handle.
                        if (class_exists($entry->get('gateway')['use'])) {
                            $entry->set('gateway', array_merge($entry->get('gateway'), [
                                'use' => $entry->get('gateway')['use']::handle(),
                            ]))->saveQuietly();
                        }

                        // When the shipping method reference is still a class, change it to the handle.
                        if ($entry->has('shipping_method') && class_exists($entry->get('shipping_method'))) {
                            $entry->set('shipping_method', $entry->get('shipping_method')::handle())->saveQuietly();
                        }
                    });
                });
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            OrderModel::query()
                ->where('gateway', '!=', null)
                ->chunk(50, function ($orders) {
                    $orders->each(function ($order) {
                        // When the gateway reference is still a class, change it to the handle.
                        if (class_exists($order->gateway['use'])) {
                            $order->gateway = array_merge($order->gateway, [
                                'use' => $order->gateway['use']::handle(),
                            ]);

                            $order->saveQuietly();
                        }

                        // When the shipping method reference is still a class, change it to the handle.
                        if (isset($order->data['shipping_method']) && class_exists($order->data['shipping_method'])) {
                            $order->data['shipping_method'] = $order->data['shipping_method']::handle();

                            $order->saveQuietly();
                        }
                    });
                });
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
