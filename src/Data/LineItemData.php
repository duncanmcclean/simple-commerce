<?php

namespace DoubleThreeDigital\SimpleCommerce\Data;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;

class LineItemData extends Data
{
    public function data(array $data, $original)
    {
        $data['variant'] = $original->variant->templatePrep();
        $data['price'] = Currency::parse($original->price);
        $data['total'] = Currency::parse($original->total);

        return $data;
    }
}
