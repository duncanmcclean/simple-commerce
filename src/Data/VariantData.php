<?php

namespace DoubleThreeDigital\SimpleCommerce\Data;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;

class VariantData extends Data
{
    public function data(array $data, $original)
    {
        collect($original->images)
            ->each(function ($image) use (&$data) {
                $data['images'][] = $image;
            });

        $original->attributes
            ->each(function (Attribute $attribute) use (&$data) {
                $data["$attribute->key"] = $attribute->value;
            });

        $data['price'] = Currency::parse($original->price);

        return $data;
    }
}
