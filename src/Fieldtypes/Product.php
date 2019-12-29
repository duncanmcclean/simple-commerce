<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Models\Product as ProductModel;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class Product extends Relationship
{
    public function formatProducts($products)
    {
        return collect($products)
            ->map(function ($product) {
                return [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'stock' => $product['stock_number'],
                ];
            });
    }

    public function getIndexItems($request)
    {
        return $this->formatProducts(ProductModel::all());
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }

    public function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }
}
