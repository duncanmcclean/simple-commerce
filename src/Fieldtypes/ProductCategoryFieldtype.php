<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class ProductCategoryFieldtype extends Relationship
{
    protected $icon = 'taxonomies';

    public function toItemArray($id)
    {
        $category = ProductCategory::find($id);

        return [
            'id'    => $category->id,
            'title' => $category->title,
        ];
    }

    public function getIndexItems($request)
    {
        return ProductCategory::all()
            ->map(function ($category) {
                return [
                    'id'    => $category->id,
                    'title' => $category->title,
                ];
            });
    }

    public function getSelectionFilters()
    {
        return [];
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('slug'),
        ];
    }

    public static function title()
    {
        return 'Product Category';
    }
}
