<?php

use DuncanMcClean\SimpleCommerce\Fieldtypes\ProductVariantsFieldtype;
use Statamic\Fields\Field;

it('can preload', function () {
    $preload = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->preload();

    expect($preload)->toBeArray()
        ->and($preload['variant_fields'])->toBeArray()->toHaveCount(2)
        ->and($preload['option_fields'])->toBeArray()->toHaveCount(3)
        ->and($preload['option_fields'][0]['handle'])->toBe('key')
        ->and($preload['option_fields'][1]['handle'])->toBe('variant')
        ->and($preload['option_fields'][2]['handle'])->toBe('price')
        ->and($preload['option_field_defaults'])->toBe([
            'key' => null,
            'variant' => null,
            'price' => null,
        ])
        ->and($preload['variant'])->toBeNull()
        ->and($preload['price'])->toBeArray();
});

it('can preload with configured option fields', function () {
    $preload = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', [
            'option_fields' => [
                [
                    'handle' => 'special_message',
                    'field' => [
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                ],
            ],
        ]))
        ->preload();

    expect($preload)->toBeArray()
        ->and($preload['variant_fields'])->toBeArray()->toHaveCount(2)
        ->and($preload['option_fields'])->toBeArray()->toHaveCount(4)
        ->and($preload['option_fields'][0]['handle'])->toBe('key')
        ->and($preload['option_fields'][1]['handle'])->toBe('variant')
        ->and($preload['option_fields'][2]['handle'])->toBe('price')
        ->and($preload['option_field_defaults'])->toBe([
            'key' => null,
            'variant' => null,
            'price' => null,
            'special_message' => null, // Only null because it's a `text` fieldtype.
        ])
        ->and($preload['variant'])->toBeNull()
        ->and($preload['price'])->toBeArray()
        ->and($preload['special_message'])->toBeNull(); // Only null because it's a `text` fieldtype.
});

it('can preprocess', function () {
    $preProcess = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->preProcess([
            'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
            'options' => [
                ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
            ],
        ]);

    expect($preProcess)->toBeArray()
        ->and($preProcess['variants'])->toBeArray()->toHaveCount(1)
        // Ensures the 'Price' field has been pre-processed.
        ->and($preProcess['options'][0]['price'])->toBeString()->toBe('10.00')
        ->and($preProcess['options'][1]['price'])->toBeString()->toBe('15.00')
        ->and($preProcess['options'][2]['price'])->toBeString()->toBe('17.99');
});

it('can process', function () {
    $process = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->process([
            'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
            'options' => [
                ['key' => 'Red', 'variant' => 'Red', 'price' => '10.00'],
                ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => '15.00'],
                ['key' => 'Blue', 'variant' => 'Blue', 'price' => '17.99'],
            ],
        ]);

    expect($process)->toBeArray()
        ->and($process['variants'])->toBeArray()->toHaveCount(1)
        // Ensures the 'Price' field has been processed.
        ->and($process['options'][0]['price'])->toBeInt()->toBe(1000)
        ->and($process['options'][1]['price'])->toBeInt()->toBe(1500)
        ->and($process['options'][2]['price'])->toBeInt()->toBe(1799);
});

it('can process with no variants and no options', function () {
    $process = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->process([
            'variants' => [],
            'options' => [],
        ]);

    expect($process)->toBeNull();
});

it('can augment', function () {
    $augment = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->augment([
            'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
            'options' => [
                ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
            ],
        ]);

    expect($augment)->toBeArray()
        ->and($augment['variants'][0]['name']->value())->toBe('Colour')
        ->and($augment['variants'][0]['values']->value())->toBe(['Red', 'Yellow', 'Blue'])
        ->and($augment['options'][0]['key']->value())->toBe('Red')
        ->and($augment['options'][0]['variant']->value())->toBe('Red')
        ->and($augment['options'][0]['price']->value())->toBe('£10.00')
        ->and($augment['options'][1]['key']->value())->toBe('Yellow')
        ->and($augment['options'][1]['variant']->value())->toBe('Yellow')
        ->and($augment['options'][1]['price']->value())->toBe('£15.00')
        ->and($augment['options'][2]['key']->value())->toBe('Blue')
        ->and($augment['options'][2]['variant']->value())->toBe('Blue')
        ->and($augment['options'][2]['price']->value())->toBe('£17.99');
});

it('can preProcessIndex with no variants', function () {
    $preProcessIndex = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->preProcessIndex([
            'variants' => [],
            'options' => [],
        ]);

    expect($preProcessIndex)->toBeString()->toBe('No variants.');
});

it('can preProcessIndex with one variant', function () {
    $preProcessIndex = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->preProcessIndex([
            'variants' => ['name' => 'Colour', 'values' => ['Red']],
            'options' => [
                ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
            ],
        ]);

    expect($preProcessIndex)->toBeString()->toBe('1 variant');
});

it('can preProcessIndex with multiple variants', function () {
    $preProcessIndex = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->preProcessIndex([
            'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
            'options' => [
                ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
            ],
        ]);

    expect($preProcessIndex)->toBeString()->toBe('3 variants');
});

it('returns extra validation rules', function () {
    $extraRules = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', [
            'option_fields' => [
                [
                    'handle' => 'size',
                    'field' => [
                        'type' => 'text',
                        'validate' => 'required,min:10,max:20',
                    ],
                ],
            ],
        ]))
        ->extraRules();

    expect($extraRules)->toBeArray()->toBe([
        'variants' => ['array'],
        'options' => ['array'],
        'variants.*.name' => ['required'],
        'variants.*.values' => ['required'],
        'options.*.key' => ['required'],
        'options.*.variant' => ['required'],
        'options.*.price' => ['required'],
        'options.*.size' => ['required,min:10,max:20'],
    ]);
});
