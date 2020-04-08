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
        return Product::find($id);
    }

    public function getIndexItems($request)
    {
        return Product::all();
    }

    public function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }

    public static function title()
    {
        return 'Product';
    }
}
