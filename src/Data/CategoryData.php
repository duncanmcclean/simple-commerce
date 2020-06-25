<?php

namespace DoubleThreeDigital\SimpleCommerce\Data;

use DoubleThreeDigital\SimpleCommerce\Models\Product;

class CategoryData extends Data
{
    public function data(array $data, $original)
    {
        $data['products'] = $original->products->map(function (Product $product) {
            return $product->templatePrep();
        });

        return $data;
    }
}
