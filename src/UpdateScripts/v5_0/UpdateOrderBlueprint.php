<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Runway;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class UpdateOrderBlueprint extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0-beta.1');
    }

    public function update()
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            Collection::find(SimpleCommerce::orderDriver()['collection'])
                ->entryBlueprints()
                ->each(function (Blueprint $blueprint) {
                    $blueprint->removeField('is_paid');
                    $blueprint->removeField('paid_date');

                    $blueprint->ensureFieldInSection('order_status', [
                        'type'      => 'order_status',
                        'display'   => 'Order Status',
                        'read_only' => true,
                        'validate'  => 'required',
                        'listable'  => true,
                    ], 'sidebar');

                    $blueprint->ensureFieldInSection('payment_status', [
                        'type'      => 'payment_status',
                        'display'   => 'Payment Status',
                        'read_only' => true,
                        'validate'  => 'required',
                        'listable'  => true,
                    ], 'sidebar');

                    $blueprint->ensureFieldInSection('status_log', [
                        'type'      => 'sc_status_log',
                        'display'   => 'Status Log',
                    ], 'sidebar');

                    $blueprint->save();
                });
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $resource = Runway::orderModel();

            $blueprint = $resource->blueprint();

            $blueprint->removeField('is_paid');
            $blueprint->removeField('paid_date');

            $blueprint->ensureFieldInSection('order_status', [
                'type'      => 'order_status',
                'display'   => 'Order Status',
                'read_only' => true,
                'validate'  => 'required',
                'listable'  => true,
            ], 'sidebar');

            $blueprint->ensureFieldInSection('payment_status', [
                'type'      => 'payment_status',
                'display'   => 'Payment Status',
                'read_only' => true,
                'validate'  => 'required',
                'listable'  => true,
            ], 'sidebar');

            $blueprint->ensureFieldInSection('status_log', [
                'type'      => 'sc_status_log',
                'display'   => 'Status Log',
            ], 'sidebar');

            $blueprint->save();
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
