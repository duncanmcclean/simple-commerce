<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Relationship;

class ShippingOptionFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcessIndex($data)
    {
        return $this->field->parent()->shippingOption()?->name();
    }
}
