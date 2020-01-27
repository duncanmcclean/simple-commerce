<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class OrderStatusFieldtype extends Relationship
{
    protected $categories = ['commerce'];
    protected $icon = 'select';

    protected function toItemArray($id)
    {
        return OrderStatus::find($id);
    }

    public function getIndexItems($request)
    {
        return OrderStatus::all();
    }

    public function getColumns()
    {
        return [
            Column::make('name'),
        ];
    }

    public static function title()
    {
        return 'Order Status';
    }
}
