<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_products_index()
    {
        $products = factory(Product::class, 5)->create();

        collect($products)
            ->each(function ($product) {
                $variant = factory(Variant::class)->create([
                    'product_id' => $product->id,
                ]);
            });

        $response = $this->get('/products');

        $response
            ->assertOk()
            ->assertSee('Products');
    }

    /** @test */
    public function can_get_product()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response
            ->assertOk()
            ->assertSee($product->title);
    }

    /** @test */
    public function cant_get_product_that_is_not_enabled()
    {
        $product = factory(Product::class)->create([
            'is_enabled' => false,
        ]);
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response
            ->assertNotFound();
    }
}
