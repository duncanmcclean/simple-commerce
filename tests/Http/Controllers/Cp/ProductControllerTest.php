<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\ProductController;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ProductControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $currency = factory(Currency::class)->create();
        Config::set('simple-commerce.currency.iso', $currency->iso);
    }

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
            ->assertSee('publish-form')
            ->assertSee('Create Product');
    }

    /** @test */
    public function can_store_product()
    {
        $this
            ->actAsSuper()
            ->post(cp_route('products.store'), [
                'title' => $this->faker->word,
                'slug' => str_slug($this->faker->word),
                'category' => [factory(ProductCategory::class)->create()->id],
                'is_enabled' => true,
                'tax_rate_id' => [factory(TaxRate::class)->create()->id],
                'needs_shipping' => true,
                'attributes_featured' => 'true',
                'variants' => [
                    [
                        'name'              => $this->faker->word,
                        'sku'               => str_slug($this->faker->word),
                        'price'             => '10',
                        'stock'             => '100',
                        'unlimited_stock'   => 'true',
                        'max_quantity'      => '5',
                        'description'       => $this->faker->realText(),
                        'images'            => [],
                        'weight'            => 100,
                        'attributes_colour' => 'Red',
                    ],
                    [
                        'name'              => $this->faker->word,
                        'sku'               => str_slug($this->faker->word),
                        'price'             => '10',
                        'stock'             => '100',
                        'unlimited_stock'   => 'true',
                        'max_quantity'      => '5',
                        'description'       => $this->faker->realText(),
                        'images'            => [],
                        'weight'            => 100,
                        'attributes_colour' => 'Yellow',
                    ],
                ],
            ])
            ->assertOk()
            ->assertSee('redirect');

        $product = Product::latest()->first();

        $this
            ->assertDatabaseHas('attributes', [
                'key'   => 'featured',
                'attributable_id' => $product->id,
                'attributable_type' => 'DoubleThreeDigital\\SimpleCommerce\\Models\\Product',
            ])
            ->assertDatabaseHas('attributes', [
                'key'   => 'colour',
                'attributable_type' => 'DoubleThreeDigital\\SimpleCommerce\\Models\\Variant',
            ]);
    }

    /** @test */
    public function can_edit_product()
    {
        $product = factory(Product::class)->create();
        $variants = factory(Variant::class, 2)->create(['product_id' => $product->id]);

        $this
            ->actAsSuper()
            ->get(cp_route('products.edit', ['product' => $product->uuid]))
            ->assertOk()
            ->assertSee($product->title);
    }

    /** @test */
    public function can_update_product()
    {
        $product = factory(Product::class)->create([
            'needs_shipping' => false,
        ]);
        $variants = factory(Variant::class, 2)->create(['product_id' => $product->id]);

        $this
            ->actAsSuper()
            ->post(cp_route('products.update', ['product' => $product->uuid]), [
                'title'             => $this->faker->word,
                'slug'              => $product->slug,
                'category'          => [$product->product_category_id],
                'is_enabled'        => $product->is_enabled,
                'tax_rate_id'       => [factory(TaxRate::class)->create()->id],
                'needs_shipping'    => true,
                'variants' => [
                    [
                        'name'              => $this->faker->word,
                        'sku'               => str_slug($this->faker->word),
                        'price'             => '10',
                        'stock'             => '100',
                        'unlimited_stock'   => 'true',
                        'max_quantity'      => 5,
                        'description'       => $this->faker->realText(),
                        'images'            => [],
                        'weight'            => 100,
                    ],
                ],
            ])
            ->assertOk();

        $this
            ->assertDatabaseHas('products', [
                'uuid' => $product->uuid,
                'needs_shipping' => true,
            ]);
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

        $this
            ->assertDatabaseMissing('products', [
                'uuid'          => $product->uuid,
            ])
            ->assertDatabaseMissing('attributes', [
                'uuid'          => $productAttribute->uuid,
            ])
            ->assertDatabaseMissing('variants', [
                'uuid'          => $variant->uuid,
            ])
            ->assertDatabaseMissing('attributes', [
                'uuid'          => $variantAttribute->uuid,
            ]);
    }
}
