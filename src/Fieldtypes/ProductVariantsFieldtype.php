<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\FieldtypeRepository;
use Statamic\Fieldtypes\Textarea;

class ProductVariantsFieldtype extends Fieldtype
{
    public function configFieldItems(): array
    {
        // todo: localize this

        return [
            'option_fields' => [
                'display' => 'Option Fields',
                'type' => 'fields',
                'instructions' => 'Configure fields that will be shown when an option is created.',
            ],
        ];
    }

    public function preload()
    {
        return [
            'variant_fields' => [
                (new Field('name', [
                    'type' => 'text',
                    'listable' => 'hidden',
                    'display' => 'Name',
                    'width' => 50,
                    'input_type' => 'text',
                    'validate' => 'required',
                ]))->toBlueprintArray(),
                (new Field('values', [
                    'type' => 'taggable',
                    'listable' => 'hidden',
                    'display' => 'Values',
                    'width' => 50,
                    'validate' => 'required',
                ]))->toPublishArray(),
            ],
            'option_fields' => array_merge(
                [
                    (new Field('variant', [
                        'type' => 'textarea',
                        'listable' => 'hidden',
                        'display' => 'Variant',
                        'read_only' => true,
                        'validate' => 'required',
                    ]))->toPublishArray(),
                    (new Field('price', [
                        'type' => 'money',
                        'read_only' => false,
                        'listable' => 'hidden',
                        'display' => 'price',
                        'validate' => 'required',
                    ]))->toPublishArray(),
                ],
                collect($this->config('option_fields'))
                    ->map(function ($field) {
                        return (
                            new Field($field['handle'], $field['field'])
                        )->toPublishArray();
                    })
                    ->toArray(),
            ),
            'variant' => resolve(Textarea::class)->preload(),
            'price' => resolve(MoneyFieldtype::class)->preload(),
        ];
    }

    public function preProcess($data)
    {
        return [
            'variants' => $this->processInsideFields($data['variants'], $this->preload()['variant_fields'], 'preProcess'),
            'options' => $this->processInsideFields($data['options'], $this->preload()['option_fields'], 'preProcess'),
        ];
    }

    public function process($data)
    {
        return [
            'variants' => $this->processInsideFields($data['variants'], $this->preload()['variant_fields'], 'process'),
            'options' => $this->processInsideFields($data['options'], $this->preload()['option_fields'], 'process'),
        ];
    }

    public static function title()
    {
        // todo: localize this
        return 'Product Variants';
    }

    public function component(): string
    {
        return 'product-variants';
    }

    public function augment($value)
    {
        return [
            'variants' => $this->processInsideFields($value['variants'], $this->preload()['variant_fields'], 'augment'),
            'options' => $this->processInsideFields($value['options'], $this->preload()['option_fields'], 'augment'),
        ];
    }

    protected function processInsideFields(array $fieldValues, array $fields, string $method)
    {
        return collect($fieldValues)
            ->map(function ($optionAttributes) use ($fields, $method) {
                return collect($optionAttributes)
                    ->map(function ($value, $key) use ($fields, $method) {
                        if ($key === 'key') {
                            return $value;
                        }

                        return collect($fields)
                            ->where('handle', $key)
                            ->map(function ($field) use ($value, $method) {
                                return (new FieldtypeRepository())
                                    ->find($field['type'])
                                    ->{$method}($value);
                            })
                            ->first();
                    })
                    ->toArray();
            })
            ->toArray();
    }
}
