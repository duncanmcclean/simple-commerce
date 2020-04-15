<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class StateFieldtype extends Relationship
{
    protected $icon = 'earth';

    protected function toItemArray($id)
    {
        $state = State::find($id);

        return [
            'id'    => $state->id,
            'title' => $state->name,
        ];
    }

    public function getIndexItems($request)
    {
        return State::all();
    }

    public function getColumns()
    {
        return [
            Column::make('name'),
        ];
    }

    public static function title()
    {
        return 'State';
    }
}
