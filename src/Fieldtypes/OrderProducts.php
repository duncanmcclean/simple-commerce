<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Facades\Product;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class OrderProducts extends Relationship
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
        return $this->formatProducts(Product::all());
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('stock'),
        ];
    }

    public function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }
}
