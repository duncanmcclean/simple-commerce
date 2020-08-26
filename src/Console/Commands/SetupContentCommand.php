<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;

class SetupContentCommand extends Command
{
    use RunsInPlease;

    protected $name = 'statamic:simple-commerce:setup-content';
    protected $description = 'Sets up default collections & taxonomies for Simple Commerce.';

    public function handle()
    {
        $this
            ->setupTaxonomies()
            ->setupCollections();
    }

    protected function setupTaxonomies()
    {
        if (!Taxonomy::handleExists(config('simple-commerce.taxonomies.product_categories'))) {
            $this->info('Creating: Product Categories');

            Taxonomy::make(config('simple-commerce.taxonomies.product_categories'))
                ->title(__('simple-commerce::messages.default_taxonomies.product_categories'))
                ->save();
        } else {
            $this->warn('Skipping: Product Categories');
        }

        return $this;
    }

    protected function setupCollections()
    {
        if (!Collection::handleExists(config('simple-commerce.collections.products'))) {
            $this->info('Creating: Products');

            Collection::make(config('simple-commerce.collections.products'))
                ->title(__('simple-commerce::messages.default_collections.products'))
                ->pastDateBehavior('public')
                ->futureDateBehavior('private')
                ->sites(['default'])
                ->routes('/products/{slug}')
                ->taxonomies(['product_categories'])
                ->save();
        } else {
            $this->warn('Skipping: Products');
        }

        if (!Collection::handleExists(config('simple-commerce.collections.customers'))) {
            $this->info('Creating: Customers');

            Collection::make(config('simple-commerce.collections.customers'))
                ->title(__('simple-commerce::messages.default_collections.customers'))
                ->sites(['default'])
                ->save();
        } else {
            $this->warn('Skipping: Customers');
        }

        if (!Collection::handleExists(config('simple-commerce.collections.orders'))) {
            $this->info('Creating: Orders');

            Collection::make(config('simple-commerce.collections.orders'))
                ->title(__('simple-commerce::messages.default_collections.orders'))
                ->sites(['default'])
                ->save();
        } else {
            $this->warn('Skipping: Orders');
        }

        if (!Collection::handleExists(config('simple-commerce.collections.coupons'))) {
            $this->info('Creating: Coupons');

            Collection::make(config('simple-commerce.collections.coupons'))
                ->title(__('simple-commerce::messages.default_collections.coupons'))
                ->sites(['default'])
                ->save();
        } else {
            $this->warn('Skipping: Coupons');
        }

        return $this;
    }
}
