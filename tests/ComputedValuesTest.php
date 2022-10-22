<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Facades\Product;

class ComputedValuesTest extends TestCase
{
    use SetupCollections;

    /** @test */
    public function product_returns_with_raw_price_value()
    {
        $product = Product::make()->price(1500);
        $product->save();

        $this->assertSame(1500, $product->resource()->raw_price);
    }
}
