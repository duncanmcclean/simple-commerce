<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

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
