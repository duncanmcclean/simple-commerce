<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;

class ProductCategoryControllerTest extends TestCase
{
    /** @test */
    public function can_show_product_category()
    {
        $category = factory(ProductCategory::class)->create();
        $product = factory(Product::class)->create([
            'product_category_id' => $category->id,
        ]);
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->get('/category/'.$category->slug);

        $response
            ->assertOk()
            ->assertSee($category->title)
            ->assertSee($product->title);
    }
}
