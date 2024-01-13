<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v6_0;

use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\UpdateScripts\UpdateScript;

class UpdateClassReferences extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        $this->updateInOrders();
    }

    protected function updateInOrders(): self
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            Collection::find(SimpleCommerce::orderDriver()['collection'])
                ->queryEntries()
                ->where('gateway', '!=', null)
                ->chunk(50, function ($orders) {
                    $orders->each(function ($entry) {
                        if (! class_exists($entry->get('gateway')['use'])) {
                            return;
                        }

                        $entry->set('gateway', array_merge($entry->get('gateway'), [
                            'use' => $entry->get('gateway')['use']::handle(),
                        ]))->saveQuietly();
                    });
                });
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            OrderModel::query()
                ->where('gateway', '!=', null)
                ->chunk(50, function ($orders) {
                    $orders->each(function ($order) {
                        if (! class_exists($order->gateway['use'])) {
                            return;
                        }

                        $order->gateway = array_merge($order->gateway, [
                            'use' => $order->gateway['use']::handle(),
                        ]);

                        $order->saveQuietly();
                    });
                });
        }

        return $this;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
