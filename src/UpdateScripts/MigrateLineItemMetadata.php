<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\UpdateScripts\UpdateScript;
use Illuminate\Support\Arr;

class MigrateLineItemMetadata extends UpdateScript
{
    protected $topLevelKeys = ['id', 'product', 'variant', 'total', 'quantity'];

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.3.0');
    }

    public function update()
    {
        // TODO: check if using entries driver
        EntryAPI::whereCollection(config('simple-commerce.collections.orders'))
            ->each(function (Entry $entry) {
                $lineItems = collect($entry->get('items'))
                    ->map(function ($lineItem) {
                        return array_merge(
                            Arr::only($lineItem, $this->topLevelKeys),
                            ['metadata' => Arr::except($lineItem, $this->topLevelKeys)]
                        );
                    })
                    ->toArray();

                $entry->data(['items' => $lineItems])->save();
            });

        $this->console()->info('Migrated line item metdata!');
    }
}
