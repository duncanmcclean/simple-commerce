<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Data;

use DoubleThreeDigital\SimpleCommerce\Data\ProductData;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ProductDataTest extends TestCase
{
    /** @test */
    public function it_can_do_all_the_things()
    {
        $currency = factory(Currency::class)->create();

        $category = factory(ProductCategory::class)->create();
        $product = factory(Product::class)->create(['product_category_id' => $category->id]);
        $variant = factory(Variant::class)->create(['product_id' => $product->id]);
        $attribute = factory(Attribute::class)->create(['key' => 'IsACd', 'value' => true, 'attributable_type' => Product::class, 'attributable_id' => $product->id]);

        $data = (new ProductData)->data($product->toArray(), $product);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('images', $data);
        $this->assertArrayHasKey('variants', $data);
        $this->assertArrayHasKey('IsACd', $data);
        $this->assertArrayHasKey('category', $data);
    }
}