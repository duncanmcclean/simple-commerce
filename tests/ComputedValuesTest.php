<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;

uses(DoubleThreeDigital\SimpleCommerce\Tests\TestCase::class);
uses(SetupCollections::class);

test('product returns with raw price value', function () {
    $product = Product::make()->price(1500);
    $product->save();

    $this->assertSame(1500, $product->resource()->raw_price);
});
