<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;

uses(DoubleThreeDigital\SimpleCommerce\Tests\TestCase::class);
uses(SetupCollections::class);

test('product returns with raw price value', function () {
    $product = Product::make()->price(1500);
    $product->save();

    expect($product->resource()->raw_price)->toBe(1500);
});
