<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Currency;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class MoneyFieldtype extends Fieldtype
{
    protected $icon = 'generic';

    public function configFieldItems(): array
    {
        return [
            'read_only' => [
                'type'         => 'toggle',
                'instructions' => __('simple-commerce::messages.fieldtypes.money.config_fields.read_only'),
                'width'        => 50,
            ],
        ];
    }

    public function preload()
    {
        return Currency::get(Site::selected());
    }

    public function preProcess($data)
    {
        if ($data !== null) {
            return substr_replace($data, '.', -2, 0);
        }

        return $data;
    }

    public function process($data)
    {
        if ($data === '' || $data === null) {
            // return (int) 0000;

            return null;
        }

        if (! str_contains($data, '.')) {
            $data = $data * 100;
        }

        return (int) str_replace('.', '', $data);
    }

    public static function title()
    {
        return __('simple-commerce::messages.fieldtypes.money.title');
    }

    public function component(): string
    {
        return 'money';
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return Currency::parse($value, Site::current());
    }

    public function preProcessIndex($value)
    {
        if (! $value) {
            return;
        }

        return Currency::parse($value, Site::selected());
    }
}
