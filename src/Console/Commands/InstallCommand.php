<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;

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
        $siteHandles = Site::all()->map->handle()->toArray();

        $productDriver = SimpleCommerce::productDriver();
        $customerDriver = SimpleCommerce::customerDriver();
        $orderDriver = SimpleCommerce::orderDriver();
        $couponDriver = SimpleCommerce::couponDriver();

        if (! Collection::handleExists($productDriver['collection'])) {
            $this->info('Creating: Products');

            Collection::make($productDriver['collection'])
                ->title(Str::title($productDriver['collection']))
                ->pastDateBehavior('public')
                ->futureDateBehavior('private')
                ->sites($siteHandles)
                ->routes('/products/{slug}')
                ->save();
        } else {
            $this->warn('Skipping: Products');
        }

        if (! Collection::handleExists($customerDriver['collection'])) {
            $this->info('Creating: Customers');

            Collection::make($customerDriver['collection'])
                ->title(Str::title($customerDriver['collection']))
                ->sites($siteHandles)
                ->titleFormats(
                    collect(Site::all())
                        ->mapWithKeys(function ($site) {
                            return [
                                $site->handle() => '{name} <{email}>',
                            ];
                        })
                        ->toArray()
                )
                ->save();
        } else {
            $this->warn('Skipping: Customers');
        }

        if (! Collection::handleExists($orderDriver['collection'])) {
            $this->info('Creating: Orders');

            Collection::make($orderDriver['collection'])
                ->title(Str::title($orderDriver['collection']))
                ->sites($siteHandles)
                ->titleFormats(
                    collect(Site::all())
                        ->mapWithKeys(function ($site) {
                            return [
                                $site->handle() => '#{order_number}',
                            ];
                        })
                        ->toArray()
                )
                ->save();
        } else {
            $this->warn('Skipping: Orders');
        }

        if (! Collection::handleExists($couponDriver['collection'])) {
            $this->info('Creating: Coupons');

            Collection::make($couponDriver['collection'])
                ->title(Str::title($couponDriver['collection']))
                ->sites($siteHandles)
                ->save();
        } else {
            $this->warn('Skipping: Coupons');
        }

        return $this;
    }
}
