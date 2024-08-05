<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use Statamic\Fields\Fieldtype;

class LineItemsFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function augment($value): array
    {
        return $value->map(fn (LineItem $lineItem) => $lineItem->toAugmentedCollection())->all();
    }
}