<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Rules\ProductExists;
use Illuminate\Http\Request;

class VariantFieldtypeController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'product' => [
                'required',
                new ProductExists,
            ],
        ], $request->all());

        $product = Product::find($request->product);

        return [
            'variants' => $product->productVariants() ?? [],
            'purchasable_type' => $product->purchasableType(),
        ];
    }
}
