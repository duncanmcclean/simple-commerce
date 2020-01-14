<?php

namespace Damcclean\Commerce\Fieldtypes;

use Statamic\Fields\Fieldtype;

class MoneyFieldtype extends Fieldtype
{
    protected $categories = ['commerce'];
    protected $icon = 'generic';

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
        return 'Money';
    }
    
    public function component(): string
    {
        return 'money';
    }
}
