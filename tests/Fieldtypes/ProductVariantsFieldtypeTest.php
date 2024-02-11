<?php

use DuncanMcClean\SimpleCommerce\Fieldtypes\ProductVariantsFieldtype;
use Statamic\Fields\Field;

it('can preload', function () {
    $preload = (new ProductVariantsFieldtype)
        ->setField(new Field('product_variants', []))
        ->preload();

    expect($preload)->toBeArray();
    expect($preload['variant_fields'])->toBeArray()->toHaveCount(2);
    expect($preload['option_fields'])->toBeArray()->toHaveCount(3);
    expect($preload['option_fields'][0]['handle'])->toBe('key');
    expect($preload['option_fields'][1]['handle'])->toBe('variant');
    expect($preload['option_fields'][2]['handle'])->toBe('price');
    expect($preload['option_field_defaults'])->toBe([]);
    expect($preload['variant'])->toBeNull();
    expect($preload['price'])->toBeArray();
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

    expect($preload)->toBeArray();
    expect($preload['variant_fields'])->toBeArray()->toHaveCount(2);
    expect($preload['option_fields'])->toBeArray()->toHaveCount(4);
    expect($preload['option_fields'][0]['handle'])->toBe('key');
    expect($preload['option_fields'][1]['handle'])->toBe('variant');
    expect($preload['option_fields'][2]['handle'])->toBe('price');
    expect($preload['option_field_defaults'])->toBe([
        'special_message' => null, // Only null because it's a `text` fieldtype.
    ]);
    expect($preload['variant'])->toBeNull();
    expect($preload['price'])->toBeArray();
    expect($preload['special_message'])->toBeNull(); // Only null because it's a `text` fieldtype.
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

    expect($preProcess)->toBeArray();
    expect($preProcess['variants'])->toBeArray()->toHaveCount(1);

    // Ensures the 'Price' field has been pre-processed.
    expect($preProcess['options'][0]['price'])->toBeString()->toBe('10.00');
    expect($preProcess['options'][1]['price'])->toBeString()->toBe('15.00');
    expect($preProcess['options'][2]['price'])->toBeString()->toBe('17.99');
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

    expect($process)->toBeArray();
    expect($process['variants'])->toBeArray()->toHaveCount(1);

    // Ensures the 'Price' field has been processed.
    expect($process['options'][0]['price'])->toBeInt()->toBe(1000);
    expect($process['options'][1]['price'])->toBeInt()->toBe(1500);
    expect($process['options'][2]['price'])->toBeInt()->toBe(1799);
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

    expect($augment)->toBeArray();
    expect($augment['variants'][0])->toBeArray()->toBe([
        'name' => 'Colour',
        'values' => ['Red', 'Yellow', 'Blue'],
    ]);
    expect($augment['options'][0])->toBeArray()->toBe([
        'key' => 'Red',
        'variant' => 'Red',
        'price' => '£10.00',
    ]);
    expect($augment['options'][1])->toBeArray()->toBe([
        'key' => 'Yellow',
        'variant' => 'Yellow',
        'price' => '£15.00',
    ]);
    expect($augment['options'][2])->toBeArray()->toBe([
        'key' => 'Blue',
        'variant' => 'Blue',
        'price' => '£17.99',
    ]);
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
