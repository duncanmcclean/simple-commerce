<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;
use Statamic\Statamic;

class MoneyFieldtype extends Fieldtype
{
    public function configFieldItems(): array
    {
        return [
            'save_zero_value' => [
                'type' => 'toggle',
                'display' => __('Save Zero Value?'),
                'instructions' => __('When the value is zero, should it be saved as zero or be left empty?'),
            ],
        ];
    }

    public function preload()
    {
        return Money::get($this->determineSite());
    }

    public function preProcess($data)
    {
        if (! $data && ! $this->config('save_zero_value', false)) {
            return null;
        }

        if (! $data) {
            $data = 0;
        }

        // Replaces the second-last character with a decimal point
        if (! str_contains($data, '.')) {
            $data = substr_replace($data, '.', -2, 0);
        }

        return $data ?? 0;
    }

    public function process($data)
    {
        if (! $data && ! $this->config('save_zero_value', false)) {
            return null;
        }

        if (! str_contains($data, '.')) {
            $data = $data * 100;
        }

        return (int) str_replace('.', '', $data ?? 0);
    }

    public function augment($value)
    {
        if (! $value && ! $this->config('save_zero_value', false)) {
            return null;
        }

        return Money::format($value ?? 0, $this->determineSite());
    }

    public function preProcessIndex($data)
    {
        if (! $data && ! $this->config('save_zero_value', false)) {
            return null;
        }

        return Money::format($data ?? 0, $this->determineSite());
    }

    private function determineSite(): \Statamic\Sites\Site
    {
        $site = Statamic::isCpRoute() ? Site::selected() : Site::current();

        if ($this->field?->parent() && method_exists($this->field->parent(), 'site')) {
            $site = $this->field->parent()->site();
        }

        return $site;
    }
}
