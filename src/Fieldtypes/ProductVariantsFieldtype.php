<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use Illuminate\Support\Arr;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\FieldtypeRepository;
use Statamic\Fields\Validator;
use Statamic\Fieldtypes\Textarea;

class ProductVariantsFieldtype extends Fieldtype
{
    public function configFieldItems(): array
    {
        return [
            'option_fields' => [
                'display' => __('Option Fields'),
                'type' => 'fields',
                'instructions' => __('Configure fields that will be shown when an option is created.'),
            ],
        ];
    }

    public function preload()
    {
        return array_merge(
            [
                'variant_fields' => $this->variantFields()->toPublishArray(),
                'option_fields' => $this->optionFields()->toPublishArray(),

                'option_field_defaults' => collect($this->config('option_fields'))
                    ->mapWithKeys(function ($field) {
                        $field = (
                            new Field($field['handle'], $field['field'])
                        );

                        return [
                            $field->handle() => $field->fieldtype()->preProcess($field->defaultValue()),
                        ];
                    })
                    ->toArray(),

                'variant' => resolve(Textarea::class)->preload(),
                'price' => resolve(MoneyFieldtype::class)->preload(),
            ],
            collect($this->config('option_fields'))
                ->mapWithKeys(function ($field) {
                    $fieldMeta = (new Field($field['handle'], $field['field']))->meta();

                    // Fix the assets fieldtype (for now!)
                    if (isset($fieldMeta['data']) && collect($fieldMeta['data'])->count() === 0) {
                        $fieldMeta['data'] = null;
                    }

                    return [
                        $field['handle'] => $fieldMeta,
                    ];
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
        $process = [
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

        if (count($process['variants']) === 0 && count($process['options']) === 0) {
            return null;
        }

        return $process;
    }

    public static function title()
    {
        return __('Product Variants');
    }

    public function component(): string
    {
        return 'product-variants';
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return [
            'variants' => $this->processInsideFields(
                fieldValues: Arr::get($value, 'variants', []),
                fields: $this->variantFields()->toPublishArray(),
                method: 'augment'
            ),
            'options' => $this->processInsideFields(
                fieldValues: Arr::get($value, 'options', []),
                fields: $this->optionFields()->toPublishArray(),
                method: 'augment'
            ),
        ];
    }

    protected function processInsideFields(array $fieldValues, array $fields, string $method): array
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
                                    ->setField(new Field($field['handle'], Arr::except($field, ['handle'])))
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
            return __('No variants.');
        }

        $optionsCount = collect($value['options'])->count();

        if ($optionsCount === 0) {
            return __('No variants.');
        } elseif ($optionsCount === 1) {
            return $optionsCount.' variant';
        } else {
            return $optionsCount.' variants';
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

    protected function variantFields(): Fields
    {
        $variantFields = [
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

        return new Fields($variantFields, $this->field()->parent(), $this->field());
    }

    protected function optionFields(): Fields
    {
        $optionFields = collect([
            [
                'handle' => 'key',
                'field' => [
                    'type' => 'hidden',
                    'listable' => 'hidden',
                    'display' => 'Key',
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
                    'validate' => ['required'],
                    'width' => 50,
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
                    'width' => 50,
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

        return new Fields($optionFields, $this->field()->parent(), $this->field());
    }
}
