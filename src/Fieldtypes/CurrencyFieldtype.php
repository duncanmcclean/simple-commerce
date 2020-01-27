<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CurrencyFieldtype extends Relationship
{
    protected $categories = ['commerce'];
    protected $icon = 'generic';

    protected function toItemArray($id)
    {
        return Currency::find($id);
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

    public static function title()
    {
        return 'Currency';
    }
}
