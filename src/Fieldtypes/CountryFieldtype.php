<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Countries;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CountryFieldtype extends Relationship
{
    protected $canCreate = false;

    protected $indexComponent = null;

    public function getIndexItems($request)
    {
        return Countries::map(function ($country) {
            return [
                'id' => $country['iso'],
                'iso' => $country['iso'],
                'name' => __($country['name']),
                'title' => __($country['name']),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('name')
                ->label(__('Name')),

            Column::make('iso')
                ->label(__('ISO Code')),
        ];
    }

    public function toItemArray($id)
    {
        $country = Countries::firstWhere('iso', $id);

        return [
            'id' => $country['iso'],
            'title' => __($country['name']),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            $country = Countries::firstWhere('iso', $item);

            return __($country['name']);
        })->join(', ');
    }

    public function augment($values)
    {
        if (! $values) {
            return null;
        }

        return Countries::firstWhere('iso', $values);
    }
}
