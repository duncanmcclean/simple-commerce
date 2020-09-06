<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;

class ProductVariantsFieldtype extends Fieldtype
{
    public function configFieldItems(): array
    {
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
                    'input_type' => 'text',
                    'type' => 'text',
                    'listable' => 'hidden',
                    'display' => 'Name',
                    'width' => 50,
                ]))->toBlueprintArray(),
                (new Field('values', [
                    'type' => 'taggable',
                    'listable' => 'hidden',
                    'display' => 'Values',
                    'width' => 50,
                ]))->toPublishArray(),
            ],
            'option_fields' => array_merge(
                [
                    (new Field('variant', [
                        'type' => 'text',
                        'listable' => 'hidden',
                        'display' => 'Variant',
                        'read_only' => true,
                    ]))->toPublishArray(),
                    (new Field('price', [
                        'type' => 'money',
                        'read_only' => false,
                        'listable' => 'hidden',
                        'display' => 'price',
                    ]))->toPublishArray(),
                ],
                collect($this->config('option_fields'))
                    ->map(function ($field) {
                        return (
                            new Field($field['handle'], $field['field'])
                        )->toPublishArray();
                    })
                    ->toArray(),
            )
        ];
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
        return 'Product Variants';
    }

    public function component(): string
    {
        return 'product-variants';
    }

    public function augment($value)
    {
        return [];
    }
}
