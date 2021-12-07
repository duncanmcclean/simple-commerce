<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Fields\Validator;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\FieldtypeRepository;
use Statamic\Fieldtypes\Textarea;

class ProductVariantsFieldtype extends Fieldtype
{
    public function configFieldItems(): array
    {
        return [
            'option_fields' => [
                'display'      => __('simple-commerce::messages.fieldtypes.product_variants.config_fields.option_fields.display'),
                'type'         => 'fields',
                'instructions' => __('simple-commerce::messages.fieldtypes.product_variants.config_fields.option_fields.instructions'),
            ],
        ];
    }

    public function preload()
    {
        return array_merge(
            [
                'variant_fields' => [
                    (new Field('name', [
                        'type'       => 'text',
                        'listable'   => 'hidden',
                        'display'    => 'Name',
                        'width'      => 50,
                        'input_type' => 'text',
                        'validate'   => 'required',
                    ]))->toPublishArray(),
                    (new Field('values', [
                        'type'     => 'taggable',
                        'listable' => 'hidden',
                        'display'  => 'Values',
                        'width'    => 50,
                        'validate' => 'required',
                    ]))->toPublishArray(),
                ],
                'option_fields' => array_merge(
                    [
                        (new Field('key', [
                            'type'      => 'hidden',
                            'listable'  => 'hidden',
                            'display'   => 'Key',
                            'read_only' => true,
                            'validate'  => 'required',
                        ]))->toPublishArray(),
                        (new Field('variant', [
                            'type'      => 'textarea',
                            'listable'  => 'hidden',
                            'display'   => 'Variant',
                            'read_only' => true,
                            'validate'  => 'required',
                            'width'     => 50,
                        ]))->toPublishArray(),
                        (new Field('price', [
                            'type'      => 'money',
                            'read_only' => false,
                            'listable'  => 'hidden',
                            'display'   => 'Price',
                            'validate'  => 'required',
                            'width'     => 50,
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
                'price'   => resolve(MoneyFieldtype::class)->preload(),
            ],
            collect($this->config('option_fields'))
                ->mapWithKeys(function ($field) {
                    return [$field['handle'] => (
                        new Field($field['handle'], $field['field'])
                    )->meta()];
                })
                ->toArray(),
        );
    }

    public function preProcess($data)
    {
        return [
            'variants' => $this->processInsideFields(
                isset($data['variants']) ? $data['variants'] : [],
                $this->preload()['variant_fields'],
                'preProcess'
            ),
            'options' => $this->processInsideFields(
                isset($data['options']) ? $data['options'] : [],
                $this->preload()['option_fields'],
                'preProcess'
            ),
        ];
    }

    public function process($data)
    {
        return [
            'variants' => $this->processInsideFields(
                $data['variants'],
                $this->preload()['variant_fields'],
                'process'
            ),
            'options' => $this->processInsideFields(
                $data['options'],
                $this->preload()['option_fields'],
                'process'
            ),
        ];
    }

    public static function title()
    {
        return __('simple-commerce::messages.fieldtypes.product_variants.title');
    }

    public function component(): string
    {
        return 'product-variants';
    }

    public function augment($value)
    {
        if (!$value) {
            return null;
        }

        return [
            'variants' => $this->processInsideFields(isset($value['variants']) ? $value['variants'] : [], $this->preload()['variant_fields'], 'augment'),
            'options'  => $this->processInsideFields(isset($value['options']) ? $value['options'] : [], $this->preload()['option_fields'], 'augment'),
        ];
    }

    protected function processInsideFields(array $fieldValues, array $fields, string $method)
    {
        return collect($fieldValues)
            ->map(function ($optionAttributeValues) use ($fields, $method) {
                $optionAttributes = collect($fields)->pluck('handle');

                return collect($optionAttributes)
                    ->mapWithKeys(function ($fieldHandle) use ($fields, $method, $optionAttributeValues) {
                        $value = $optionAttributeValues[$fieldHandle] ?? null;

                        $fieldValue = collect($fields)
                            ->where('handle', $fieldHandle)
                            ->map(function ($field) use ($value, $method) {
                                return (new FieldtypeRepository())
                                    ->find($field['type'])
                                    ->{$method}($value);
                            })
                            ->first();

                        return [$fieldHandle => $fieldValue];
                    })
                    ->toArray();
            })
            ->toArray();
    }

    public static function docsUrl()
    {
        return 'https://simple-commerce.duncanmcclean.com/product-variants';
    }

    public function preProcessIndex($value)
    {
        if (! $value) {
            return __('simple-commerce::messages.product_has_no_variants');
        }

        $optionsCount = collect($value['options'])->count();

        if ($optionsCount === 0) {
            return __('simple-commerce::messages.product_has_no_variants');
        } elseif ($optionsCount === 1) {
            return $optionsCount.' '.__('simple-commerce::messages.product_variants_singular');
        } else {
            return $optionsCount.' '.__('simple-commerce::messages.product_variants_plural');
        }
    }

    public function extraRules(): array
    {
        $preload = $this->preload();

        $variantFieldRules = collect($preload['variant_fields'])
            ->pluck('validate', 'handle')
            ->filter()
            ->mapWithKeys(function ($validate, $handle) {
                return ["variants.*.$handle" => Validator::explodeRules($validate)];
            })
            ->toArray();

        $optionFieldRules = collect($preload['option_fields'])
            ->pluck('validate', 'handle')
            ->filter()
            ->mapWithKeys(function ($validate, $handle) {
                return ["options.*.$handle" => Validator::explodeRules($validate)];
            })
            ->toArray();

        return array_merge([
            'variants' => ['array'],
            'options' => ['array'],
        ], $variantFieldRules, $optionFieldRules);
    }
}
