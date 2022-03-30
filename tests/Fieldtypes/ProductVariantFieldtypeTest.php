<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductVariantFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ProductVariantFieldtypeTest extends TestCase
{
    use SetupCollections;

    /** @test */
    public function can_preload_and_return_api_route()
    {
        $preload = (new ProductVariantFieldtype())->preload();

        $this->assertIsArray($preload);
        $this->assertStringContainsString('simple-commerce/fieldtype-api/product-variant', $preload['api']);
    }

    /** @test */
    public function can_preprocess_value_with_new_format()
    {
        $preProcess = (new ProductVariantFieldtype())->preProcess([
            'product' => 'abcdefg',
            'variant' => 123456789,
        ]);

        $this->assertSame($preProcess, [
            'product' => 'abcdefg',
            'variant' => 123456789,
        ]);
    }

    /** @test */
    public function can_preproccess_value_with_old_format()
    {
        $preProcess = (new ProductVariantFieldtype())->preProcess('abcdefg');

        $this->assertSame($preProcess, [
            'product' => null,
            'variant' => 'abcdefg',
        ]);
    }

    /** @test */
    public function that_augmentation_throws_exception_if_old_format()
    {
        $this->expectException("\Exception");

        $augment = (new ProductVariantFieldtype())->augment('abcdefg');
    }

    /** @test */
    public function that_augmentation_returns_null_if_purcaseable_type_is_product()
    {
        $product = Product::make()->save();

        $augment = (new ProductVariantFieldtype())->augment([
            'product' => $product->id,
            'variant' => 'One',
        ]);

        $this->assertNull($augment);
    }

    /** @test */
    public function that_augmentation_returns_null_if_variant_does_not_exist()
    {
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

        $this->assertNull($augment);
    }

    /** @test */
    public function that_augmentation_returns_variant_data()
    {
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

        $this->assertIsArray($augment);

        $this->assertArrayHasKey('key', $augment);
        $this->assertArrayHasKey('variant', $augment);
    }
}
