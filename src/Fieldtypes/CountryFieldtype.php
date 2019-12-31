<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Models\Country;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CountryFieldtype extends Relationship
{
    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }

    public function getIndexItems($request)
    {
        return Country::all();
    }

    public function getColumns()
    {
        return [
            Column::make('name'),
        ];
    }
}
