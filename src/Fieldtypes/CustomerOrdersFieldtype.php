<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Fields\Fieldtype;

class CustomerOrdersFieldtype extends Fieldtype
{
    protected $icon = 'select';

    public function preload()
    {
        return cp_route('fieldtype-data.customer-orders');
    }

    public function preProcess($data)
    {
        return $data;
    }

    public function process($data)
    {
        return $data;
    }

    public static function title()
    {
        return 'Customer Orders';
    }

    public function component(): string
    {
        return 'customer-orders';
    }
}
