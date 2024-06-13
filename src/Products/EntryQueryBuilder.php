<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use Statamic\Stache\Query\EntryQueryBuilder as QueryEntryQueryBuilder;

class EntryQueryBuilder extends QueryEntryQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($entry) => Product::fromEntry($entry));
    }

    public function wherePurchaseableType(ProductType $type): self
    {
        match ($type) {
            ProductType::Product => $this->whereNull('product_variants'),
            ProductType::Variant => $this->whereNotNull('product_variants'),
        };

        return $this;
    }
}
