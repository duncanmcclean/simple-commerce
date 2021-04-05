<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;

class InstallCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:install';
    protected $description = 'Install Simple Commerce';

    public function handle()
    {
        $this
            ->publishBlueprints()
            ->publishConfigurationFile()
            ->setupCollections();
    }

    protected function publishBlueprints(): self
    {
        $this->info('Publishing Blueprints');

        $this->callSilent('vendor:publish', [
            '--tag' => 'simple-commerce-blueprints',
        ]);

        return $this;
    }

    protected function publishConfigurationFile(): self
    {
        $this->info('Publishing Config file');

        $this->callSilent('vendor:publish', [
            '--tag' => 'simple-commerce-config',
        ]);

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
