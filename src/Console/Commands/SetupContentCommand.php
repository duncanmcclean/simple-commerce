<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;

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

        if (!Taxonomy::handleExists(config('simple-commerce.taxonomies.order_statuses', 'order_statuses'))) {
            $this->info('Creating: Order Statuses');

            Taxonomy::make(config('simple-commerce.taxonomies.order_statuses', 'order_statuses'))
                ->title(__('simple-commerce::messages.default_taxonomies.order_statuses'))
                ->save();

            Term::make()
                ->taxonomy(config('simple-commerce.taxonomies.order_statuses', 'order_statuses'))
                ->slug('cart')
                ->data([
                    'title' => __('simple-commerce::messages.default_terms.cart'),
                ])
                ->save();

            Term::make()
                ->taxonomy(config('simple-commerce.taxonomies.order_statuses', 'order_statuses'))
                ->slug('completed')
                ->data([
                    'title' => __('simple-commerce::messages.default_terms.completed'),
                ])
                ->save();

            Term::make()
                ->taxonomy(config('simple-commerce.taxonomies.order_statuses', 'order_statuses'))
                ->slug('refunded')
                ->data([
                    'title' => __('simple-commerce::messages.default_terms.refunded'),
                ])
                ->save();
        } else {
            $this->warn('Skipping: Order Statuses');
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
                // ->taxonomies(['order_statuses'])
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
