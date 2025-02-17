<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Http\Resources\CP\Orders\Order as OrderResource;
use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;

class OrderActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Order::find($item);
        });
    }

    protected function getItemData($order, $context): array
    {
        $order = $order->fresh();

        return array_merge((new OrderResource($order))->resolve()['data'], [
            'itemActions' => Action::for($order, $context),
        ]);
    }
}
