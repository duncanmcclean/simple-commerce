<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Events\EntryBlueprintFound;

class EnsureProductFields
{
    public function handle(EntryBlueprintFound $event)
    {
        if (SimpleCommerce::usingDefaultTaxDriver() && ! $event->blueprint->hasField('tax_class')) {
            $event->blueprint->ensureField('tax_class', [
                'type' => 'tax_class',
                'display' => 'Tax Class',
                'instructions' => __('Determines how this product is taxed.'),
                'listable' => 'hidden',
                'max_items' => 1,
                'create' => true,
                'validate' => 'required',
            ], 'sidebar');
        }
    }
}
