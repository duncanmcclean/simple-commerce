<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Currency;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class MoneyFieldtype extends Fieldtype
{
    protected $icon = 'generic';

    public function configFieldItems(): array
    {
        return [
            'read_only' => [
                'type' => 'toggle',
                'instructions' => __('Should this field be read only?'),
                'width' => 50,
            ],
            'save_zero_value' => [
                'type' => 'toggle',
                'display' => __('Save Zero Value?'),
                'instructions' => __('When the value is zero, should it be saved as zero or be left empty?'),
                'width' => 50,
            ],
        ];
    }

    public function preload()
    {
        return Currency::get(Site::selected());
    }

    public function preProcess($data)
    {
        if (! $data) {
            return $this->config('save_zero_value', false)
                ? 0
                : null;
        }

        // Replaces the second-last character with a decimal point
        if (! str_contains($data, '.')) {
            $data = substr_replace($data, '.', -2, 0);
        }

        return $data;
    }

    public function process($data)
    {
        if ($data === '' || $data === null) {
            return $this->config('save_zero_value', false)
                ? 0
                : null;
        }

        if (! str_contains($data, '.')) {
            $data = $data * 100;
        }

        return (int) str_replace('.', '', $data);
    }

    public static function title()
    {
        return __('Money');
    }

    public function component(): string
    {
        return 'money';
    }

    public function augment($value)
    {
        if (empty($value)) {
            return $this->config('save_zero_value', false)
                ? Currency::parse(0, Site::selected())
                : null;
        }

        return Currency::parse($value, Site::current());
    }

    public function preProcessIndex($value)
    {
        if (! $value) {
            return $this->config('save_zero_value', false)
                ? Currency::parse(0, Site::selected())
                : null;
        }

        return Currency::parse($value, Site::selected());
    }
}
