<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

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
            ->setupCollections()
            ->setDefaultPreferences();
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

    protected function setupCollections(): self
    {
        $siteHandles = Site::all()->map->handle()->toArray();

        $productDriver = SimpleCommerce::productDriver();
        $customerDriver = SimpleCommerce::customerDriver();
        $orderDriver = SimpleCommerce::orderDriver();

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

        return $this;
    }

    protected function setDefaultPreferences(): self
    {
        $this->info('Setting default CP Nav preferences');

        $path = resource_path('preferences.yaml');

        $defaultPreferences = File::exists($path)
            ? YAML::file($path)->parse()
            : [];

        // If the user has already set their own preferences, we don't want to override them.
        if (isset($defaultPreferences['nav'])) {
            $this->warn('You already have default CP Nav preferences set. Skipping.');

            return $this;
        }

        $customerCollection = isset(SimpleCommerce::customerDriver()['collection'])
            ? SimpleCommerce::customerDriver()['collection']
            : 'customers';

        $orderCollection = isset(SimpleCommerce::orderDriver()['collection'])
            ? SimpleCommerce::orderDriver()['collection']
            : 'orders';

        $defaultPreferences['nav'] = [
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'content' => [
                    'content::collections' => [
                        'action' => '@modify',
                        'children' => [
                            "content::collections::{$customerCollection}" => '@hide',
                            "content::collections::{$orderCollection}" => '@hide',
                        ],
                    ],
                ],
                'simple_commerce' => '@inherit',
                'fields' => '@inherit',
                'tools' => '@inherit',
                'users' => '@inherit',
            ],
        ];

        $defaultPreferences['collctions'][$orderCollection]['columns'] = [
            'title', 'order_status', 'payment_status', 'grand_total', 'customer',
        ];

        File::put($path, YAML::dump($defaultPreferences));

        return $this;
    }
}
