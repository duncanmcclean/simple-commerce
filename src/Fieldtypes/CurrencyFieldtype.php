<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CurrencyFieldtype extends Relationship
{
    protected $icon = 'generic';

    protected function toItemArray($id)
    {
        $currency = Currency::find($id);

        return [
            'id' => $currency->id,
            'title' => "$currency->symbol $currency->name",
        ];
    }

    public function getIndexItems($request)
    {
        return Currency::all();
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
        return 'Currency';
    }
}
