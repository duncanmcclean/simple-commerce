<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Data;

use DoubleThreeDigital\SimpleCommerce\Data\CategoryData;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CategoryDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $currency = factory(Currency::class)->create();
        Config::set('simple-commerce.curreny.iso', $currency->iso);
    }

    /** @test */
    public function it_can_do_all_the_things()
    {
        $category = factory(ProductCategory::class)->create();
        $products = factory(Product::class, 5)->create();
        foreach ($products as $product) {
            $product = Product::find($product['id']);
            $product->productCategories()->attach($category->id);

            $variant = factory(Variant::class)->create(['product_id' => $product->id]);
        }

        $data = (new CategoryData)->data($category->toArray(), $category);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('products', $data);
    }
}