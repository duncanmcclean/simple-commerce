<?php

namespace DoubleThreeDigital\SimpleCommerce\Data;

use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;

class ProductData extends Data
{
    public function data(array $data, $original)
    {
        $data['images'] = [];

        $original->attributes
            ->each(function (Attribute $attribute) use (&$data) {
                $data["$attribute->key"] = $attribute->value;
            });

        $data['variants'] = collect($original->variants)
            ->map(function (Variant $variant) {
                return $variant->templatePrep();
            })->toArray();

        $data['categories'] = $original->productCategories->toArray();  

        return $data;
    }
}
