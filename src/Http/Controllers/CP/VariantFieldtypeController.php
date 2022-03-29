<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Rules\ProductExists;
use Illuminate\Http\Request;

class VariantFieldtypeController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'product' => ['required', new ProductExists()],
        ], $request->all());

        $product = Product::find($request->product);

        return [
            'purchasable_type' => $product->purchasableType(),
            'variants'         => $product->has('product_variants') ? $product->get('product_variants')['options'] : [],
        ];
    }
}
