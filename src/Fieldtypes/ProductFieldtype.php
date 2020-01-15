<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class ProductFieldtype extends Relationship
{
    protected $categories = ['commerce'];
    protected $icon = 'entries';

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
        ];
    }

    public function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }

    public static function title()
    {
        return 'Product';
    }
}
