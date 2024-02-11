<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class TaxCategoryFieldtype extends Relationship
{
    protected $canCreate = false;

    protected function configFieldItems(): array
    {
        return [
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'type' => 'integer',
                'width' => 50,
                'read_only' => true,
                'default' => 1,
            ],
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                'type' => 'radio',
                'default' => 'default',
                'options' => [
                    'default' => __('Stack Selector'),
                    'select' => __('Select Dropdown'),
                    'typeahead' => __('Typeahead Field'),
                ],
                'width' => 50,
                'read_only' => true,
                'default' => 'select',
            ],
        ];
    }

    public function getIndexItems($request)
    {
        return TaxCategory::all()->map(function ($taxCategory) {
            return [
                'id' => $taxCategory->id(),
                'name' => $taxCategory->name(),
                'title' => $taxCategory->name(),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('name')
                ->label(__('Name')),
        ];
    }

    public function toItemArray($id)
    {
        $taxCategory = TaxCategory::find($id);

        return [
            'id' => $taxCategory->id(),
            'title' => $taxCategory->name(),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            $taxCategory = TaxCategory::find($item);

            return [
                'id' => $taxCategory->id(),
                'title' => $taxCategory->name(),
                'edit_url' => $taxCategory->editUrl(),
            ];
        });
    }
}
