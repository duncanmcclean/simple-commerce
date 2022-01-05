<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Fieldtypes\Relationship;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\CP\Column;

class GatewaysFieldtype extends Relationship
{
    protected $canSearch = false;

    protected $canCreate = false;

    public function getIndexItems($request)
    {
        return collect(SimpleCommerce::gateways())
            ->map(function ($instance) {
                return [
                    'id'      => $instance['class'],
                    'title'   => $instance['name'],
                ];
            })
            ->whereNotNull()
            ->toArray();
        
    }
     
    protected function toItemArray($id)
    {
        if (! $instance = collect(SimpleCommerce::gateways())->firstWhere('class', $id)) {
            return null;
        }

        return [
            'id'      => $instance['class'],
            'title'   => $instance['name'],
        ];
    }
 
    protected function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            return $this->toItemArray($item);
        });
    }

    public function rules(): array
    {
        if ($this->config('max_items') === 1) {
            return ['string'];
        }

        return parent::rules();
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return $this->toItemArray($value);
    }
}
