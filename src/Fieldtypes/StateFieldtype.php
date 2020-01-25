<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\State;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class StateFieldtype extends Relationship
{
    protected $categories = ['commerce'];
    protected $icon = 'earth';

    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
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
