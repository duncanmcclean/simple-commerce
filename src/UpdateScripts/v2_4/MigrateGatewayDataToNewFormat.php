<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v2_4;

use DoubleThreeDigital\SimpleCommerce\Orders\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\UpdateScripts\UpdateScript;

class MigrateGatewayDataToNewFormat extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.4.0-beta.1');
    }

    public function update()
    {
        if (SimpleCommerce::orderDriver()['repository'] !== Order::class) {
            $this->console()->error('Gateway data could not be migrated. You are not using the default Order driver.');
        }

        EntryAPI::whereCollection(SimpleCommerce::orderDriver()['collection'])
            ->each(function (Entry $entry) {
                if (is_string($entry->get('gateway')) && $entry->get('gateway')) {
                    $gatewayClass = $entry->get('gateway');
                    $gatewayData = $entry->get('gateway_data') ?? [];

                    $entry
                        ->set('gateway', [
                            'use' => $gatewayClass,
                            'data' => $gatewayData,
                        ])
                        ->set('gateway_data', null)
                        ->save();
                }
            });

        $this->console()->info('Migrated gateway data on orders!');
    }
}
