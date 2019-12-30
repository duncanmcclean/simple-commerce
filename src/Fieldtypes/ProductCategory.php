<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Models\ProductCategory as ProductCategoryModel;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class ProductCategory extends Relationship
{
    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }

    public function getIndexItems($request)
    {
        return ProductCategoryModel::all();
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }
}
