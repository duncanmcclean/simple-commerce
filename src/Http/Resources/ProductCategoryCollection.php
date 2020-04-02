<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection;
    }
}
