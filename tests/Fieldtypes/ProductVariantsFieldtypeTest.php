<?php

namespace Tests\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Fieldtypes\ProductVariantsFieldtype;
use Statamic\Fields\Field;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProductVariantsFieldtypeTest extends TestCase
{
    #[Test]
    public function can_preload()
    {
        $preload = (new ProductVariantsFieldtype)
            ->setField(new Field('product_variants', []))
            ->preload();

        $this->assertIsArray($preload);

        $this->assertCount(2, $preload['variant_fields']);
        $this->assertCount(3, $preload['option_fields']);

        $this->assertEquals('key', $preload['option_fields'][0]['handle']);
        $this->assertEquals('variant', $preload['option_fields'][1]['handle']);
        $this->assertEquals('price', $preload['option_fields'][2]['handle']);

        $this->assertEquals($preload['option_field_defaults'], []);
        $this->assertNull($preload['variant']);
        $this->assertIsArray($preload['price']);
    }

    #[Test]
    public function can_preload_with_configured_option_fields()
    {
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

        $this->assertIsArray($preload);

        $this->assertCount(2, $preload['variant_fields']);
        $this->assertCount(4, $preload['option_fields']);

        $this->assertEquals('key', $preload['option_fields'][0]['handle']);
        $this->assertEquals('variant', $preload['option_fields'][1]['handle']);
        $this->assertEquals('price', $preload['option_fields'][2]['handle']);

        $this->assertEquals($preload['option_field_defaults'], [
            'special_message' => null, // Only null because it's a `text` fieldtype.
        ]);

        $this->assertNull($preload['variant']);
        $this->assertIsArray($preload['price']);
        $this->assertNull($preload['special_message']); // Only null because it's a `text` fieldtype.
    }

    #[Test]
    public function can_pre_process()
    {
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

        $this->assertIsArray($preProcess);

        $this->assertIsArray($preProcess['variants']);
        $this->assertCount(1, $preProcess['variants']);

        // Ensures the 'Price' field has been processed.
        $this->assertEquals($preProcess['options'][0]['price'], '10.00');
        $this->assertEquals($preProcess['options'][1]['price'], '15.00');
        $this->assertEquals($preProcess['options'][2]['price'], '17.99');
    }

    #[Test]
    public function can_process()
    {
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

        $this->assertIsArray($process);

        $this->assertIsArray($process['variants']);
        $this->assertCount(1, $process['variants']);

        // Ensures the 'Price' field has been processed.
        $this->assertEquals($process['options'][0]['price'], 1000);
        $this->assertEquals($process['options'][1]['price'], 1500);
        $this->assertEquals($process['options'][2]['price'], 1799);
    }

    #[Test]
    public function can_process_with_no_variants_and_no_options()
    {
        $process = (new ProductVariantsFieldtype)
            ->setField(new Field('product_variants', []))
            ->process([
                'variants' => [],
                'options' => [],
            ]);

        $this->assertNull($process);
    }

    #[Test]
    public function can_augment()
    {
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

        $this->assertIsArray($augment);

        $this->assertEquals([
            'name' => 'Colour',
            'values' => ['Red', 'Yellow', 'Blue'],
        ], collect($augment['variants'][0])->map->value()->all());

        $this->assertEquals([
            'key' => 'Red',
            'variant' => 'Red',
            'price' => '£10.00',
        ], collect($augment['options'][0])->map->value()->all());

        $this->assertEquals([
            'key' => 'Yellow',
            'variant' => 'Yellow',
            'price' => '£15.00',
        ], collect($augment['options'][1])->map->value()->all());

        $this->assertEquals([
            'key' => 'Blue',
            'variant' => 'Blue',
            'price' => '£17.99',
        ], collect($augment['options'][2])->map->value()->all());
    }

    #[Test]
    public function can_pre_process_index_with_no_variants()
    {
        $preProcessIndex = (new ProductVariantsFieldtype)
            ->setField(new Field('product_variants', []))
            ->preProcessIndex([
                'variants' => [],
                'options' => [],
            ]);

        $this->assertIsString($preProcessIndex);
        $this->assertEquals('No variants.', $preProcessIndex);
    }

    #[Test]
    public function can_pre_process_index_with_one_variant()
    {
        $preProcessIndex = (new ProductVariantsFieldtype)
            ->setField(new Field('product_variants', []))
            ->preProcessIndex([
                'variants' => ['name' => 'Colour', 'values' => ['Red']],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                ],
            ]);

        $this->assertIsString($preProcessIndex);
        $this->assertEquals('1 variant', $preProcessIndex);
    }

    #[Test]
    public function can_pre_process_index_with_multiple_variants()
    {
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

        $this->assertIsString($preProcessIndex);
        $this->assertEquals('3 variants', $preProcessIndex);
    }

    #[Test]
    public function returns_extra_validation_rules()
    {
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

        $this->assertIsArray($extraRules);

        $this->assertEquals([
            'variants' => ['array'],
            'options' => ['array'],
            'variants.*.name' => ['required'],
            'variants.*.values' => ['required'],
            'options.*.key' => ['required'],
            'options.*.variant' => ['required'],
            'options.*.price' => ['required'],
            'options.*.size' => ['required,min:10,max:20'],
        ], $extraRules);
    }
}
