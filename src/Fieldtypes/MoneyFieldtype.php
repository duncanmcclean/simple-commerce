<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Facades\Site;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;

class MoneyFieldtype extends Fieldtype
{
    protected $icon = 'generic';

    public function configFieldItems(): array
    {
        return [
            'read_only' => [
                'type'         => 'toggle',
                'instructions' => __('simple-commerce::fieldtypes.money.config_fields.read_only'),
                'width'        => 50,
            ],
            'store_as_float' => [
                'type'         => 'toggle',
                'instructions' => __('simple-commerce::fieldtypes.money.config_fields.store_as_float'),
                'width'        => 50,
            ],
        ];
    }

    public function preload()
    {
        return Currency::get(Site::current());
    }

    public function preProcess($data)
    {
        if (! $this->config('store_as_float') && $data !== null) {
            return substr_replace($data, '.', -2, 0);
        }

        return $data;
    }

    public function process($data)
    {
        if (! $this->config('store_as_float')) {
            return (int) str_replace('.', '', $data);
        }

        return (int) $data;
    }

    public static function title()
    {
        return __('simple-commerce::fieldtypes.money.title');
    }

    public function component(): string
    {
        return 'money';
    }

    public function augment($value)
    {
        return Currency::parse($value, Site::current());
    }
}
