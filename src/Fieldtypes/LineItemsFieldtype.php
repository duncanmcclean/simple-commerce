<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Fields\Fieldtype;

class LineItemsFieldtype extends Fieldtype
{
    protected $icon = 'generic';

    public function preload()
    {
        return [
            'currency' => Currency::primary(),
        ];
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
        return 'Line Items';
    }

    public function component(): string
    {
        return 'line-items';
    }
}
