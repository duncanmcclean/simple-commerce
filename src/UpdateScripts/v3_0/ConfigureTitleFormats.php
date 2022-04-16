<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v3_0;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Fields\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class ConfigureTitleFormats extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.0.0-beta.1');
    }

    public function update()
    {
        if (! isset(SimpleCommerce::orderDriver()['collection']) || ! isset(SimpleCommerce::customerDriver()['collection'])) {
            return;
        }

        $this
            ->setupTitleFormatForOrders()
            ->setupTitleFormatForCustomers();
    }

    protected function setupTitleFormatForOrders(): self
    {
        Collection::find(SimpleCommerce::orderDriver()['collection'])
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

        Collection::find(SimpleCommerce::orderDriver()['collection'])
            ->entryBlueprints()
            ->each(function (Blueprint $blueprint) {
                $blueprint->removeField('title', 'main');

                $blueprint->ensureFieldInSection('order_number', [
                    'type' => 'hidden',
                ], $blueprint->sections()->last()->handle());

                $blueprint->save();
            });

        return $this;
    }

    protected function setupTitleFormatForCustomers(): self
    {
        Collection::find(SimpleCommerce::customerDriver()['collection'])
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

        Collection::find(SimpleCommerce::customerDriver()['collection'])
            ->entryBlueprints()
            ->each(function (Blueprint $blueprint) {
                $blueprint->removeField('title', 'main');
                $blueprint->save();
            });

        return $this;
    }
}
