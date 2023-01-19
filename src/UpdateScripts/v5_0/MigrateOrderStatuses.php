<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Entry;
use Statamic\UpdateScripts\UpdateScript;

class MigrateOrderStatuses extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0-beta.1');
    }

    public function update()
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            Entry::query()
                ->where('collection', SimpleCommerce::orderDriver()['collection'])
                ->get()
                ->each(function ($entry) {
                    if ($entry->get('is_paid') === true) {
                        $entry->set('order_status', OrderStatus::Placed->value);
                        $entry->set('payment_status', PaymentStatus::Paid->value);

                        if ($entry->get('is_shipped') === true) {
                            $entry->set('order_status', OrderStatus::Dispatched->value);
                        }

                        if ($entry->get('is_refunded') === true) {
                            $entry->set('payment_status', PaymentStatus::Refunded->value);
                        }

                        $entry->save();

                        return;
                    }

                    $entry->set('order_status', OrderStatus::Cart->value);
                    $entry->set('payment_status', PaymentStatus::Unpaid->value);

                    $entry->save();
                });
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            // TODO: Implement for Eloquent orders.
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
