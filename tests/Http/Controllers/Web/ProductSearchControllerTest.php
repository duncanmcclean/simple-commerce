<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_search_index()
    {
        $response = $this->get('/products/search');

        $response
            ->assertOk()
            ->assertSee('Search');
    }

    /** @test */
    public function can_get_search_results()
    {
        $product = factory(Product::class)->create();

        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->get('/products/search/results?query='.$product->title);

        $response
            ->assertOk()
            ->assertSee($product->title);
    }

    /** @test */
    public function cant_get_search_results_of_query_with_no_results()
    {
        $product = factory(Product::class)->create();

        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->get('/products/search/results?query=qwerty');

        $response
            ->assertOk()
            ->assertDontSee($product->title);
    }
}
