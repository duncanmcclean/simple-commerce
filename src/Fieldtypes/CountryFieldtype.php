<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CountryFieldtype extends Relationship
{
    protected $icon = 'earth';

    protected function toItemArray($id)
    {
        $country = Country::find($id);

        return [
            'id'    => $country->id,
            'title' => $country->name,
        ];
    }

    public function getIndexItems($request)
    {
        return Country::all()
            ->map(function (Country $country) {
                return [
                    'id'    => $country->id,
                    'title' => $country->name,
                ];
            });
    }

    public function getColumns()
    {
        return [
            Column::make('name'),
            Column::make('iso'),
        ];
    }

    public static function title()
    {
        return 'Country';
    }
}
