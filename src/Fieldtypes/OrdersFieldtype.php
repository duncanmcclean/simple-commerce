<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use Statamic\Fieldtypes\Relationship;

class OrdersFieldtype extends Relationship
{
    protected $canCreate = false;

    protected function toItemArray($id)
    {
        $order = Order::find($id);

        return [
            'id' => $order->id(),
            'title' => "#{$order->orderNumber()}",
        ];
    }

    public function getIndexItems($request)
    {
        // TODO: Implement getIndexItems() method.
    }

    public function augment($values)
    {
        return collect($values)->map(fn ($id) => Order::find($id)?->toShallowAugmentedArray())->filter()->all();
    }
}