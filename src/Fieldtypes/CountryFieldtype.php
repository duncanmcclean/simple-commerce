<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Countries;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CountryFieldtype extends Relationship
{
    protected $canCreate = false;

    public function getIndexItems($request)
    {
        return Countries::map(function ($country) {
            return [
                'id'   => $country['iso'],
                'iso'  => $country['iso'],
                'name' => $country['name'],
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('name'),

            Column::make('iso')
                ->label('ISO Code'),
        ];
    }

    public function toItemArray($id)
    {
        $country = Countries::firstWhere('iso', $id);

        return [
            'id' => $country['iso'],
            'title' => $country['name'],
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            $country = Countries::firstWhere('iso', $item);

            return $country['name'];
        })->join(', ');
    }

    public function rules(): array
    {
        if ($this->config('max_items') === 1) {
            return ['string', 'in:' . implode(',', Countries::values()->pluck('iso')->toArray())];
        }

        return parent::rules();
    }
}
