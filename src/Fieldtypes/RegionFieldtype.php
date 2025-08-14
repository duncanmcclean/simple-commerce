<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Regions;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class RegionFieldtype extends Relationship
{
    protected $canCreate = false;

    protected $indexComponent = null;

    public function getIndexItems($request)
    {
        return Regions::map(function ($region) {
            return [
                'id' => $region['id'],
                'country_iso' => $region['country_iso'],
                'country_name' => __(Countries::findByRegion($region)->first()['name']),
                'name' => __($region['name']),
                'title' => __($region['name']),
            ];
        })->sortBy('country_name')->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('name')
                ->label(__('Name')),

            Column::make('country_name')
                ->label(__('Country')),
        ];
    }

    public function toItemArray($id)
    {
        $region = Regions::find($id);

        return [
            'id' => $region['id'],
            'title' => __($region['name']),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            $region = Regions::find($item);

            return __($region['name']);
        })->join(', ');
    }

    public function augment($values)
    {
        if (! $values) {
            return null;
        }

        return Regions::find($values);
    }
}
