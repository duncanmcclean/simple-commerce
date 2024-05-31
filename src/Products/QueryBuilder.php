<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use Illuminate\Support\Arr;
use Statamic\Stache\Query\EntryQueryBuilder;

class QueryBuilder extends EntryQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($entry) => $this->fromEntry($entry));
    }

    protected function fromEntry($entry)
    {
        $product = Product::make()
            ->entry($entry)
            ->id($entry->id());

        if ($entry->has('price') || $entry->originValues()->has('price')) {
            $product->price($entry->value('price'));
        }

        if ($entry->has('product_variants') || $entry->originValues()->has('product_variants')) {
            $product->productVariants($entry->value('product_variants'));
        }

        if ($entry->has('stock') || $entry->originValues()->has('stock')) {
            $product->stock($entry->value('stock'));
        }

        //        if (SimpleCommerce::isUsingStandardTaxEngine() && ($entry->has('tax_category') || $entry->originValues()->has('tax_category'))) {
        //            $product->taxCategory($entry->value('tax_category'));
        //        }

        return $product->data(array_merge(
            Arr::except(
                $entry->values()->toArray(),
                ['price', 'product_variants', 'stock', 'tax_category']
            ),
            [
                'site' => optional($entry->site())->handle(),
                'slug' => $entry->slug(),
                'published' => $entry->published(),
            ]
        ));
    }
}
