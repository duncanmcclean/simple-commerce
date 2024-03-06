<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Validation\Rule;
use Statamic\CP\Column;
use Statamic\Facades\Site;
use Statamic\Fieldtypes\Relationship;

class ShippingMethodFieldtype extends Relationship
{
    protected $canCreate = false;

    protected $indexComponent = null;

    protected function configFieldItems(): array
    {
        return [
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'type' => 'hidden',
                'default' => 1,
            ],
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                'type' => 'hidden',
                'default' => 'select',
            ],
        ];
    }

    public function getIndexItems($request)
    {
        $site = Site::selected();

        return SimpleCommerce::shippingMethods($site->handle())
            ->map(function ($shippingMethod) {
                return [
                    'id' => $shippingMethod['class'],
                    'name' => $shippingMethod['name'],
                    'title' => $shippingMethod['name'],
                ];
            })
            ->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('name')
                ->label(__('Name')),
        ];
    }

    public function toItemArray($value)
    {
        $site = Site::selected();

        $shippingMethod = SimpleCommerce::shippingMethods($site->handle())
            ->where('handle', $value)
            ->first();

        if (! $shippingMethod) {
            return null;
        }

        return [
            'id' => $shippingMethod['class'],
            'title' => $shippingMethod['name'],
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            $site = Site::selected();

            $shippingMethod = SimpleCommerce::shippingMethods($site->handle())
                ->where('handle', $item)
                ->first();

            if (! $shippingMethod) {
                return null;
            }

            return $shippingMethod['name'];
        })->join(', ');
    }

    public function rules(): array
    {
        if ($this->config('max_items') === 1) {
            $site = Site::selected();

            return [
                'string',
                Rule::in(SimpleCommerce::shippingMethods($site->handle())->pluck('class')->toArray()),
            ];
        }

        return parent::rules();
    }
}
