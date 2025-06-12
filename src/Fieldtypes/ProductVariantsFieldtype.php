<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use Illuminate\Support\Arr;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Validator;

class ProductVariantsFieldtype extends Fieldtype
{
    protected static $title = 'Product Variants';

    public function configFieldItems(): array
    {
        return [
            'columns' => [
                'type' => 'radio',
                'display' => __('Columns'),
                'instructions' => __('Configure the number of columns used when displaying variant options.'),
                'default' => 2,
                'options' => [
                    1 => __('One Column'),
                    2 => __('Two Columns'),
                ],
            ],
            'option_fields' => [
                'type' => 'fields',
                'display' => __('Option Fields'),
                'instructions' => __('Configure fields for variant options.'),
            ],
        ];
    }

    public function preload()
    {
        return [
            'variants' => [
                'fields' => $this->variantFields()->toPublishArray(),
                'new' => $this->variantFields()->meta()->all(),
                'existing' => collect(Arr::get($this->field->value(), 'variants', []))
                    ->map(fn (array $variant) => $this->variantFields()->addValues($variant)->preProcess()->meta())
                    ->all(),
            ],
            'options' => [
                'fields' => $this->optionFields()->toPublishArray(),
                'defaults' => $this->optionFields()->all()
                    ->map(fn ($field) => $field->fieldtype()->preProcess($field->defaultValue()))
                    ->all(),
                'new' => $this->optionFields()->meta()->all(),
                'existing' => collect(Arr::get($this->field->value(), 'options', []))
                    ->map(fn (array $option) => $this->optionFields()->addValues($option)->preProcess()->meta())
                    ->all(),
            ],
        ];
    }

    public function preProcess($data)
    {
        $defaultVariant = [
            'name' => '',
            'values' => [],
        ];

        return [
            'variants' => collect(Arr::get($data, 'variants', [$defaultVariant]))
                ->map(fn ($variant) => $this->variantFields()->addValues($variant)->preProcess()->values()->all())
                ->all(),
            'options' => collect(Arr::get($data, 'options'))
                ->map(fn ($option) => $this->optionFields()->addValues($option)->preProcess()->values()->all())
                ->all(),
        ];
    }

    public function process($data)
    {
        if (empty($data) || count($data['variants']) === 0 || count($data['options']) === 0) {
            return null;
        }

        return [
            'variants' => collect(Arr::get($data, 'variants'))
                ->map(fn ($variant) => $this->variantFields()->addValues($variant)->process()->values()->filter()->all())
                ->all(),
            'options' => collect(Arr::get($data, 'options'))
                ->map(fn ($option) => $this->optionFields()->addValues($option)->process()->values()->filter()->all())
                ->all(),
        ];
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return [
            'variants' => collect(Arr::get($value, 'variants'))
                ->map(fn ($option) => $this->variantFields()->addValues($option)->augment()->values()->all())
                ->all(),
            'options' => collect(Arr::get($value, 'options'))
                ->map(fn ($option) => $this->optionFields()->addValues($option)->augment()->values()->all())
                ->all(),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return __('No variants.');
        }

        $count = collect($data['options'])->count();

        return match ($count) {
            0 => __('No variants.'),
            1 => __(':count variant', ['count' => $count]),
            default => __(':count variants', ['count' => $count]),
        };
    }

    public function extraRules(): array
    {
        return [
            "{$this->field->handle()}.variants" => ['array'],
            "{$this->field->handle()}.options" => ['array'],
            ...$this->variantFields()->all()
                ->flatMap(fn ($field) => [
                    "{$this->field->handle()}.variants.*.{$field->handle()}" => Validator::explodeRules($field->get('validate')),
                ])
                ->filter()
                ->all(),
            ...$this->optionFields()->all()
                ->flatMap(fn ($field) => [
                    "{$this->field->handle()}.options.*.{$field->handle()}" => Validator::explodeRules($field->get('validate')),
                ])
                ->filter()
                ->all(),
        ];
    }

    private function variantFields(): Fields
    {
        $fields = [
            [
                'handle' => 'name',
                'field' => [
                    'type' => 'text',
                    'listable' => 'hidden',
                    'display' => 'Name',
                    'width' => 50,
                    'input_type' => 'text',
                    'validate' => ['required'],
                ],
            ],
            [
                'handle' => 'values',
                'field' => [
                    'type' => 'taggable',
                    'listable' => 'hidden',
                    'display' => 'Values',
                    'width' => 50,
                    'validate' => ['required'],
                ],
            ],
        ];

        return new Fields($fields, $this->field()->parent(), $this->field());
    }

    private function optionFields(): Fields
    {
        $fields = collect([
            [
                'handle' => 'key',
                'field' => [
                    'type' => 'hidden',
                    'listable' => 'hidden',
                    'display' => 'Key',
                    'visibility' => 'hidden',
                    'always_save' => true,
                    'read_only' => true,
                    'validate' => ['required'],
                ],
            ],
            [
                'handle' => 'variant',
                'field' => [
                    'type' => 'textarea',
                    'listable' => 'hidden',
                    'display' => 'Variant',
                    'read_only' => true,
                    'visibility' => 'hidden',
                    'always_save' => true,
                    'validate' => ['required'],
                ],
            ],
            [
                'handle' => 'price',
                'field' => [
                    'type' => 'money',
                    'read_only' => false,
                    'listable' => 'hidden',
                    'display' => 'Price',
                    'validate' => ['required'],
                ],
            ],
        ])
            ->merge($this->config('option_fields', []))
            ->when($this->config('localizable', false), function ($fields) {
                return $fields->map(function (array $field) {
                    $field['field']['localizable'] = true;

                    return $field;
                });
            })
            ->toArray();

        return new Fields($fields, $this->field()->parent(), $this->field());
    }
}
