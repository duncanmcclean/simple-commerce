<?php

Route::get('/products', function() {
    return new \DoubleThreeDigital\SimpleCommerce\Http\Resources\ProductCollection(
        \DoubleThreeDigital\SimpleCommerce\Models\Product::with('attributes', 'variants.attributes', 'productCategory', 'variants')->paginate()
    );
});

Route::get('/product-categories', function () {
    return new \DoubleThreeDigital\SimpleCommerce\Http\Resources\ProductCategoryCollection(
        \DoubleThreeDigital\SimpleCommerce\Models\ProductCategory::with('products')->paginate()
    );
});
