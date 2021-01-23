<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Exception;
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
                'instructions' => __('simple-commerce::fieldtypes.money.config_fields.read_only'),
                'width'        => 50,
            ],
        ];
    }

    public function preload()
    {
        // TODO: Figure out a way of getting the current locale when being shown in CP

        return Currency::get(Site::current());
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
            return (int) 0000;
        }

        if (! str_contains($data, '.')) {
            $data = $data * 100;
        }

        return (int) str_replace('.', '', $data);
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
        if (! $value) {
            $value = 0;
        }

        return Currency::parse($value, Site::current());
    }

    public function preProcessIndex($value)
    {
        if (! $value) {
            return;
        }

        return $this->augment($value);
    }
}
