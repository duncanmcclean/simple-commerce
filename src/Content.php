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
        if (! Taxonomy::handleExists('product_categories')) {
            Taxonomy::make('product_categories')
                ->title(__('simple-commerce::messages.default_taxonomies.product_categories'))
                ->save();
        }

        return $this;
    }

    protected function setupCollections()
    {
        if (! Collection::handleExists('products')) {
            Collection::make('products')
                ->title(__('simple-commerce::messages.default_collections.products'))
                ->pastDateBehavior('public')
                ->futureDateBehavior('private')
                ->sites(['default'])
                ->routes('/products/{slug}')
                ->taxonomies(['product_categories'])
                ->save();
        }

        if (! Collection::handleExists('customers')) {
            Collection::make('customers')
                ->title(__('simple-commerce::messages.default_collections.customers'))
                ->sites(['default'])
                ->save();
        }

        if (! Collection::handleExists('orders')) {
            Collection::make('orders')
                ->title(__('simple-commerce::messages.default_collections.orders'))
                ->sites(['default'])
                ->save();
        }

        if (! Collection::handleExists('coupons')) {
            Collection::make('coupons')
                ->title(__('simple-commerce::messages.default_collections.coupons'))
                ->sites(['default'])
                ->save();
        }

        return $this;
    }
}