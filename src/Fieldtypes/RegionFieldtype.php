<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Regions;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class RegionFieldtype extends Relationship
{
    protected $canCreate = false;

    public function getIndexItems($request)
    {
        return Regions::map(function ($region) {
            return [
                'id'   => $region['id'],
                'country_iso'  => $region['country_iso'],
                'country_name' => Countries::findByRegion($region)->first()['name'],
                'name' => $region['name'],
            ];
        })->sortBy('country_name')->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('name'),
            Column::make('country_name')
                ->label('Country'),
        ];
    }

    public function toItemArray($id)
    {
        $region = Regions::find($id);

        return [
            'id' => $region['id'],
            'title' => $region['name'],
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            $region = Regions::find($item);

            return $region['name'];
        })->join(', ');
    }

    public function rules(): array
    {
        if ($this->config('max_items') === 1) {
            return ['string', 'in:' . implode(',', Regions::values()->pluck('id')->toArray())];
        }

        return parent::rules();
    }
}
