<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ShippingOptionFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcessIndex($data)
    {
        return $this->field->parent()->shippingOption()?->name();
    }
}
