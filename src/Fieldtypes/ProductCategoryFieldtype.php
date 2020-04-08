<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class ProductCategoryFieldtype extends Relationship
{
    protected $icon = 'taxonomies';

    protected function toItemArray($id)
    {
        $category = ProductCategory::find($id);

        return [
            'id' => $category->id,
            'title' => $category->title,
            'slug' => $category->slug,
        ];
    }

    public function getIndexItems($request)
    {
        return ProductCategory::all();
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }

    public static function title()
    {
        return 'Product Category';
    }
}
