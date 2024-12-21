<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use Statamic\Fields\Fieldtype;

class PaymentDetailsFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($data)
    {
        $order = $this->field->parent();

        return [
            //
        ];
    }

    public function process($data): null
    {
        return null;
    }
}
