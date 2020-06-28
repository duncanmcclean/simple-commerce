<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Blueprint as FacadesBlueprint;
use Statamic\Facades\Entry;

class PurchasableRepository
{
    /**
     * This class is useed to check what's purchaseable and to get data from a purchasable id
     */

    public function get(EntriesEntry $entry): array
    {
        $isEntryPurchasable = $this->isWholeEntryPurchasable($entry);
        $isGridPurchasable = $this->isGridFieldPurchasable($entry);

        return [
            'type'  => $isEntryPurchasable ? 'entry' : ($isGridPurchasable ? 'grid' : null),
            'data'  => $isEntryPurchasable ? $entry->data() : ($isGridPurchasable ? $isGridPurchasable : null),
            'id'    => $isEntryPurchasable ? $this->makePurchasableId('entry', $entry->id(), $entry->data()['sku']) : ($isGridPurchasable ? $this->makePurchasableId('grid', $entry->id(), $isGridPurchasable['sku']): null),
        ];
    }

    public function find(string $purchaseableId): array
    {
        $purchaseableId = explode($purchaseableId, '__');
        $entry = Entry::find($purchaseableId[0], 'products');

        return $this->get($entry);
    }

    protected function makePurchasableId(string $type, string $entryId, string $sku = ''): string
    {
        if ($type === 'entry') {
            return $entryId;
        }

        if ($type === 'field') {
            return $entryId.'__'.$sku;
        }
    }

    protected function isWholeEntryPurchasable(EntriesEntry $entry): bool
    {
        $blueprint = FacadesBlueprint::find($entry->blueprint());

        return isset($blueprint->contents()['purchasable']) ? true : false;
    }

    protected function isGridFieldPurchasable(EntriesEntry $entry)
    {
        $blueprint = FacadesBlueprint::find($entry->blueprint());

        $field = $blueprint
            ->fields()
            ->items()
            ->where('purchasable', true)
            ->first();

        return isset($field) ? $field : false; 
    }
}