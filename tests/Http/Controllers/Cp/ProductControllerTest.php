<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\ProductController;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ProductControllerTest extends TestCase
{
    /** @test */
    public function can_get_product_index()
    {
        $products = factory(Product::class, 5)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('products.index'))
            ->assertOk()
            ->assertSee($products[0]['title'])
            ->assertSee($products[1]['title'])
            ->assertSee($products[2]['title'])
            ->assertSee($products[3]['title'])
            ->assertSee($products[4]['title']);
    }

    /** @test */
    public function can_get_product_index_with_no_products()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('products.index'))
            ->assertOk();
    }

    /** @test */
    public function can_create_product()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('products.create'))
            ->assertOk()
            ->assertSee('<publish-form')
            ->assertSee('Create Product');
    }

    /** @test */
    public function can_store_product()
    {
        $this
            ->actAsSuper()
            ->post(cp_route('products.store'), [
                'title'             => $this->faker->word,
                'slug'              => str_slug($this->faker->word),
                'description'       => $this->faker->realText(),
                'category'          => factory(ProductCategory::class)->create()->id,
                'is_enabled'        => 'true',
                'attributes_weight' => '100g',
                'variants' => [
                    [
                        'name'              => $this->faker->word,
                        'sku'               => str_slug($this->faker->word),
                        'price'             => '10',
                        'stock'             => '100',
                        'unlimited_stock'   => 'true',
                        'max_quantity'      => '5',
                        'description'       => $this->faker->realText(),
                        'attributes_colour'  => 'Red',
                    ],
                    [
                        'name'              => $this->faker->word,
                        'sku'               => str_slug($this->faker->word),
                        'price'             => '10',
                        'stock'             => '100',
                        'unlimited_stock'   => 'true',
                        'max_quantity'      => '5',
                        'description'       => $this->faker->realText(),
                        'attributes_colour'  => 'Yellow',
                    ],
                ],
            ])
            ->assertOk();

        $this
            ->assertDatabaseHas('attributes', [
                'key' => 'weight',
                'value' => '"100g"',
            ])
            ->assertDatabaseHas('attributes', [
                'key' => 'colour',
                'value' => '"Yellow"',
            ]);
    }

    /** @test */
    public function can_edit_product()
    {
        $product = factory(Product::class)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('products.edit', ['product' => $product->uuid]))
            ->assertOk()
            ->assertSee($product->title);
    }

    /** @test */
    public function can_update_product()
    {
        $product = factory(Product::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('products.update', ['product' => $product->uuid]), [
                'title'                 => $this->faker->words(3),
                'slug'                  => $product->slug,
                'description'           => $this->faker->realText(),
                'product_category_id'   => $product->product_category_id,
                'is_enabled'            => 'true',
                'variants' => [
                    [
                        'name'              => $this->faker->word,
                        'sku'               => str_slug($this->faker->word),
                        'price'             => '10',
                        'stock'             => '100',
                        'unlimited_stock'   => 'true',
                        'max_quantity'      => 5,
                        'description'       => $this->faker->realText(),
                        'attributes_colour' => 'Red',
                    ],
                ],
            ])
            ->assertOk();
    }

    /** @test */
    public function can_destroy_product()
    {
        $product = factory(Product::class)->create();
        $productAttribute = factory(Attribute::class)->create([
            'attributable_type' => Product::class,
            'attributable_id'   => $product->id,
        ]);
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);
        $variantAttribute = factory(Attribute::class)->create([
            'attributable_type' => Variant::class,
            'attributable_id'   => $variant->id,
        ]);

        $this
            ->actAsSuper()
            ->delete(cp_route('products.destroy', ['product' => $product->uuid]))
            ->assertOk();

        // We're not doing 'assertMissing' because we use soft deletes on these modelss
        $this
            ->assertDatabaseHas('products', [
                'uuid'          => $product->uuid,
            ])
            ->assertDatabaseHas('attributes', [
                'uuid'          => $productAttribute->uuid,
            ])
            ->assertDatabaseHas('variants', [
                'uuid'          => $variant->uuid,
            ])
            ->assertDatabaseHas('attributes', [
                'uuid'          => $variantAttribute->uuid,
            ]);
    }
}
