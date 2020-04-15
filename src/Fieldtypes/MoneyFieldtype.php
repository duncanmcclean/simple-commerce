<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Fields\Fieldtype;

class MoneyFieldtype extends Fieldtype
{
    protected $icon = 'generic';

    protected $configFields = [
        'read_only' => [
            'type' => 'toggle',
            'instructions' => 'Should this field be read only?',
            'width' => 50,
        ],
    ];

    public function preload()
    {
        return Currency::primary();
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
        return 'Money';
    }

    public function component(): string
    {
        return 'money';
    }
}
