<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ProductCategoryControllerTest extends TestCase
{
    /** @test */
    public function can_get_category_index()
    {
        $categories = factory(ProductCategory::class, 5)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('product-categories.index'))
            ->assertOk()
            ->assertSee($categories[0]['title'])
            ->assertSee($categories[1]['title'])
            ->assertSee($categories[2]['title'])
            ->assertSee($categories[3]['title'])
            ->assertSee($categories[4]['title']);
    }

    /** @test */
    public function can_get_category_index_with_no_categories()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('product-categories.index'))
            ->assertOK();
    }

    /** @test */
    public function can_create_category()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('product-categories.create'))
            ->assertOk('publish-form')
            ->assertSee('Create Product Category');
    }

    /** @test */
    public function can_store_category()
    {
        $this
            ->actAsSuper()
            ->post(cp_route('product-categories.store'), [
                'title' => 'Clothing',
                'slug'  => 'clothing',
            ])
            ->assertOk();
    }

    /** @test */
    public function can_show_category()
    {
        $category = factory(ProductCategory::class)->create();
        $products = factory(Product::class, 5)->create([
            'product_category_id' => $category->id
        ]);

        $this
            ->actAsSuper()
            ->get(cp_route('product-categories.show', ['category' => $category->uuid]))
            ->assertOk()
            ->assertSee($products[0]['title'])
            ->assertSee($products[1]['title'])
            ->assertSee($products[2]['title'])
            ->assertSee($products[3]['title'])
            ->assertSee($products[4]['title']);
    }

    /** @test */
    public function can_edit_category()
    {
        $category = factory(ProductCategory::class)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('product-categories.edit', ['category' => $category->uuid]))
            ->assertOk()
            ->assertSee('publish-form')
            ->assertSee($category->title);
    }

    /** @test */
    public function can_update_category()
    {
        $category = factory(ProductCategory::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('product-categories.update', ['category' => $category->uuid]), [
                'title' => 'Bedding',
                'slug' => 'bedding',
            ])
            ->assertOk();
    }

    /** @test */
    public function can_destroy_category()
    {
        $category = factory(ProductCategory::class)->create();

        $this
            ->actAsSuper()
            ->delete(cp_route('product-categories.destroy', ['category' => $category->uuid]))
            ->assertOK();

        $this
            ->assertDatabaseMissing('product_categories', [
                'uuid' => $category->uuid,
            ]);
    }
}
