<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v2_3;

use DoubleThreeDigital\SimpleCommerce\Orders\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class AddBlueprintFields extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.3.0-beta.2');
    }

    public function update()
    {
        $this->updateOrderBlueprint();
    }

    protected function updateOrderBlueprint()
    {
        if (SimpleCommerce::orderDriver()['repository'] !== Order::class) {
            $this->console()->error("Could not migrate order blueprint. You're not using the entry content driver.");
        }

        $orderCollection = SimpleCommerce::orderDriver()['collection'];
        $orderCollectionSingular = str_singular($orderCollection);

        $blueprint = Blueprint::find("collections.{$orderCollection}.{$orderCollectionSingular}");

        if (! $blueprint) {
            $this->console()->error('Failed to update order blueprint.');
        }

        $contents = $blueprint->fileData();

        // Add metadata field to line items
        foreach ($contents['sections'] as $sectionKey => $section) {
            foreach ($section['fields'] as $sectionFieldKey => $sectionField) {
                if ($sectionField['handle'] === 'items') {
                    $metaDataFieldAlreadyExists = collect($sectionField['field']['fields'])
                        ->where('handle', 'metadata')
                        ->count();

                    if ($metaDataFieldAlreadyExists === 0) {
                        $contents['sections'][$sectionKey]['fields'][$sectionFieldKey]['field']['fields'][] = [
                            'handle' => 'metadata',
                            'field' => [
                                'type' => 'array',
                                'listable' => false,
                                'display' => 'Metadata',
                                'mode' => 'dynamic',
                                'icon' => 'array',
                            ],
                        ];
                    }
                }
            }
        }

        $blueprint
            ->setContents($contents)
            ->save();

        $this->console()->info('Successfully updated order blueprint.');
    }
}
