<?php

namespace Damcclean\Commerce\Fieldtypes;

use Damcclean\Commerce\Models\OrderStatus;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class OrderStatusFieldtype extends Relationship
{
    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
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
}
