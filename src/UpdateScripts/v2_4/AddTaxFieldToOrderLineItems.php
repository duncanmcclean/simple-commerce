<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v2_4;

use DoubleThreeDigital\SimpleCommerce\Orders\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class AddTaxFieldToOrderLineItems extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('v2.4.0-beta.4');
    }

    public function update()
    {
        if (SimpleCommerce::orderDriver()['repository'] !== Order::class) {
            $this->console()->error("Could not add 'Line Item Tax' field to Order blueprint(s). You're not using the entry content driver.");
        }

        $ordersCollection = Collection::find(SimpleCommerce::orderDriver()['collection']);

        $ordersCollection->entryBlueprints()->each(function (Blueprint $blueprint) {
            $contents = $blueprint->contents();

            // Add tax field to order blueprint
            foreach ($contents['sections'] as $sectionKey => $section) {
                foreach ($section['fields'] as $sectionFieldKey => $sectionField) {
                    if ($sectionField['handle'] === 'items') {
                        $taxFieldAlreadyExists = collect($sectionField['field']['fields'])
                            ->where('handle', 'tax')
                            ->count();

                        if ($taxFieldAlreadyExists === 0) {
                            $contents['sections'][$sectionKey]['fields'][$sectionFieldKey]['field']['fields'][] = [
                                'handle' => 'tax',
                                'field' => [
                                    'type' => 'sc_line_items_tax',
                                ],
                            ];
                        }
                    }
                }
            }

            $blueprint->setContents($contents)->save();
        });

        $this->console()->info("Added hidden 'Line Item Tax' field to Order blueprint(s).");
    }
}
