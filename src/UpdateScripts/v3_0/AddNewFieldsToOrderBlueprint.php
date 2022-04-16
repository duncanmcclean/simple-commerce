<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v3_0;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class AddNewFieldsToOrderBlueprint extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.0.0-beta.1');
    }

    public function update()
    {
        if (! isset(SimpleCommerce::orderDriver()['collection']) || ! isset(SimpleCommerce::customerDriver()['collection'])) {
            return;
        }

        Collection::find(SimpleCommerce::orderDriver()['collection'])
            ->entryBlueprints()
            ->each(function (Blueprint $blueprint) {
                $blueprint->ensureFieldInSection('gateway', [
                    'display' => 'Gateway',
                    'type' => 'gateway',
                    'read_only' => true,
                ], 'sidebar');

                $blueprint->ensureFieldInSection('shipping_method', [
                    'display' => 'Shipping Method',
                    'type' => 'shipping_method',
                    'read_only' => true,
                ], 'sidebar');

                $blueprint->save();
            });

        $this->console()->info('Simple Commerce has added two new fields to your Order blueprint: `gateway` and `shipping_method`.');
    }
}
