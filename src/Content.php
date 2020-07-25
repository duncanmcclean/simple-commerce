<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;

class Content
{
    public function setup()
    {
        $this
            ->setupTaxonomies()
            ->setupCollections();
    }

    protected function setupTaxonomies()
    {
        if (! Taxonomy::handleExists(config('simple-commerce.taxonomies.product_categories'))) {
            Taxonomy::make(config('simple-commerce.taxonomies.product_categories'))
                ->title(__('simple-commerce::messages.default_taxonomies.product_categories'))
                ->save();
        }

        return $this;
    }

    protected function setupCollections()
    {
        if (! Collection::handleExists(config('simple-commerce.collections.products'))) {
            Collection::make(config('simple-commerce.collections.products'))
                ->title(__('simple-commerce::messages.default_collections.products'))
                ->pastDateBehavior('public')
                ->futureDateBehavior('private')
                ->sites(['default'])
                ->routes('/products/{slug}')
                ->taxonomies(['product_categories'])
                ->save();
        }

        if (! Collection::handleExists(config('simple-commerce.collections.customers'))) {
            Collection::make(config('simple-commerce.collections.customers'))
                ->title(__('simple-commerce::messages.default_collections.customers'))
                ->sites(['default'])
                ->save();
        }

        if (! Collection::handleExists(config('simple-commerce.collections.orders'))) {
            Collection::make(config('simple-commerce.collections.orders'))
                ->title(__('simple-commerce::messages.default_collections.orders'))
                ->sites(['default'])
                ->save();
        }

        if (! Collection::handleExists(config('simple-commerce.collections.coupons'))) {
            Collection::make(config('simple-commerce.collections.coupons'))
                ->title(__('simple-commerce::messages.default_collections.coupons'))
                ->sites(['default'])
                ->save();
        }

        return $this;
    }
}