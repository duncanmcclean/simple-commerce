<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Models\Currency;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CurrencyFieldtype extends Relationship
{
    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }

    public function getIndexItems($request)
    {
        return Currency::all();
    }

    public function getColumns()
    {
        return [
            Column::make('name'),
        ];
    }
}
