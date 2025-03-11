<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Fields\Blueprint;

class EnsureProductFields
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! $this->isProductBlueprint($event->blueprint)) {
            return;
        }

        if (config('statamic.simple-commerce.products.digital_products') && ! $event->blueprint->hasField('type')) {
            $event->blueprint->ensureField('type', [
                'type' => 'button_group',
                'display' => 'Product Type',
                'instructions' => __('Used to determine how the product is delivered.'),
                'options' => [
                    'physical' => __('Physical'),
                    'digital' => __('Digital'),
                ],
                'default' => 'physical',
                'validate' => 'required',
            ], 'sidebar');
        }

        if (! $event->blueprint->hasField('price') && ! $event->blueprint->hasField('product_variants')) {
            $event->blueprint->ensureField('price', [
                'type' => 'money',
                'display' => 'Price',
                'instructions' => config('statamic.simple-commerce.taxes.price_includes_tax')
                    ? __('Enter the price of the product, inclusive of tax.')
                    : __('Enter the price of the product, exclusive of tax.'),
                'listable' => 'hidden',
                'validate' => 'required',
            ], 'sidebar');
        }

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

    private function isProductBlueprint(Blueprint $blueprint): bool
    {
        $collections = config('statamic.simple-commerce.products.collections');

        return in_array($blueprint->namespace(), collect($collections)->map(fn ($collection) => "collections.{$collection}")->all());
    }
}
