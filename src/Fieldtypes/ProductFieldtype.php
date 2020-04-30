<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class ProductFieldtype extends Relationship
{
    protected $icon = 'entries';

    protected function toItemArray($id)
    {
        $product = Product::find($id);

        return [
            'id'    => $product->id,
            'title' => $product->title,
        ];
    }

    public function getIndexItems($request)
    {
        return Product::all();
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('slug'),
            Column::make('variant_count')->label('Variants'),
        ];
    }

    public static function title()
    {
        return 'Product';
    }
}
