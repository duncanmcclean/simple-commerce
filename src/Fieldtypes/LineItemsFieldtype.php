<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use Illuminate\Support\Str;
use Statamic\Fields\Fieldtype;

class LineItemsFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function augment($value): array
    {
        return $value->map->toAugmentedArray()->all();
    }

    public function preProcessIndex($data): string
    {
        return $data->count().' '.Str::plural('line item', $data->count());
    }
}
