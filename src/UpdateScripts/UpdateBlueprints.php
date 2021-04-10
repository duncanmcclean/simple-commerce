<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts;

use Statamic\Facades\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class UpdateBlueprints extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.3.0');
    }

    public function update()
    {
        $this->updateOrderBlueprint();
    }

    protected function updateOrderBlueprint()
    {
        $orderCollection = config('simple-commerce.collections.orders');
        $orderCollectionSingular = str_singular($orderCollection);

        $blueprint = Blueprint::find("collections.{$orderCollection}.{$orderCollectionSingular}");

        if (! $blueprint) {
            $this->console()->error("Failed to update order blueprint.");
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

        $this->console()->info("Successfully updated order blueprint.");
    }
}
