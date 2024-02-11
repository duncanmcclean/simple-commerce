<?php

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Fieldtypes\ProductVariantFieldtype;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;

uses(SetupCollections::class);

test('can preload and return api route', function () {
    $preload = (new ProductVariantFieldtype())->preload();

    expect($preload)->toBeArray();
    expect($preload['api'])->toContain('simple-commerce/fieldtype-api/product-variant');
});

test('can preprocess value with new format', function () {
    $preProcess = (new ProductVariantFieldtype())->preProcess([
        'product' => 'abcdefg',
        'variant' => 123456789,
    ]);

    $this->assertSame($preProcess, [
        'product' => 'abcdefg',
        'variant' => 123456789,
    ]);
});

test('can preproccess value with old format', function () {
    $preProcess = (new ProductVariantFieldtype())->preProcess('abcdefg');

    $this->assertSame($preProcess, [
        'product' => null,
        'variant' => 'abcdefg',
    ]);
});

test('that augmentation throws exception if old format', function () {
    $this->expectException("\Exception");

    $augment = (new ProductVariantFieldtype())->augment('abcdefg');
});

test('that augmentation returns null if purcaseable type is product', function () {
    $product = Product::make()->save();

    $augment = (new ProductVariantFieldtype())->augment([
        'product' => $product->id,
        'variant' => 'One',
    ]);

    expect($augment)->toBeNull();
});

test('that augmentation returns null if variant does not exist', function () {
    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colour',
                    'values' => ['Yellow'],
                ],
                [
                    'name' => 'Size',
                    'values' => ['Large'],
                ],
            ],
            'options' => [
                ['key' => 'Yellow_Large', 'variant' => 'Yellow, Large'],
            ],
        ]);

    $product->save();

    $augment = (new ProductVariantFieldtype())->augment([
        'product' => $product->id,
        'variant' => 'Yellow_Small',
    ]);

    expect($augment)->toBeNull();
});

test('that augmentation returns variant data', function () {
    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colour',
                    'values' => ['Yellow'],
                ],
                [
                    'name' => 'Size',
                    'values' => ['Large'],
                ],
            ],
            'options' => [
                ['key' => 'Yellow_Large', 'variant' => 'Yellow, Large'],
            ],
        ]);

    $product->save();

    $augment = (new ProductVariantFieldtype())->augment([
        'product' => $product->id,
        'variant' => 'Yellow_Large',
    ]);

    expect($augment)->toBeArray();

    $this->assertArrayHasKey('key', $augment);
    $this->assertArrayHasKey('variant', $augment);
});
