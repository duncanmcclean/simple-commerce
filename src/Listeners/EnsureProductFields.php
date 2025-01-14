<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Fields\Blueprint;

class EnsureProductFields
{
    public function handle(EntryBlueprintFound $event)
    {
        if ($this->isProductBlueprint($event->blueprint) && SimpleCommerce::usingDefaultTaxDriver() && ! $event->blueprint->hasField('tax_class')) {
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

    protected function isProductBlueprint(Blueprint $blueprint): bool
    {
        $collections = config('statamic.simple-commerce.products.collections');

        return in_array($blueprint->namespace(), collect($collections)->map(fn ($collection) => "collections.{$collection}")->all());
    }
}
