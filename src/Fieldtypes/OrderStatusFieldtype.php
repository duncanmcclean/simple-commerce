<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class OrderStatusFieldtype extends Relationship
{
    protected $icon = 'select';

    public function toItemArray($id)
    {
        $status = OrderStatus::find($id);

        return [
            'id'    => $status->id,
            'title' => $status->name,
        ];
    }

    public function getIndexItems($request)
    {
        return OrderStatus::all()
            ->map(function ($orderStatus) {
                return [
                    'id'    => $orderStatus->id,
                    'title' => $orderStatus->name,
                ];
            });
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
