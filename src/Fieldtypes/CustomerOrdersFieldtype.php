<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Models\Order;
use Statamic\Fields\Fieldtype;

class CustomerOrdersFieldtype extends Fieldtype
{
    protected $categories = ['commerce'];
    protected $icon = 'select';

    public function preload()
    {
        return cp_route('customer-order');
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
