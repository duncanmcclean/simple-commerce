<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class TaxRateFieldtype extends Relationship
{
    public function toItemArray($id)
    {
        $rate = TaxRate::find($id);

        return [
            'id'    => $rate->id,
            'title' => $rate->name,
        ];
    }

    public function getIndexItems($request)
    {
        return TaxRate::all()
            ->map(function ($rate) {
                return [
                    'id'    => $rate->id,
                    'title' => $rate->name,
                    'description' => is_null($rate->description) ? '&mdash;' : $rate->description,
                ];
            });
    }

    public function getSelectionFilters()
    {
        return ['id', 'title', 'description'];
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('description'),
        ];
    }

    public static function title()
    {
        return 'Tax Rate';
    }
}
