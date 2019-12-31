<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Models\ProductCategory;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class ProductCategoryFieldtype extends Relationship
{
    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
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
}
