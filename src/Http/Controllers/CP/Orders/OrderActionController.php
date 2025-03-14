<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Http\Resources\CP\Orders\Order as OrderResource;
use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;

class OrderActionController extends ActionController
{
    use ExtractsFromOrderFields;

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Order::find($item);
        });
    }

    protected function getItemData($item, $context): array
    {
        $order = $item->fresh();

        $blueprint = $order->blueprint();

        [$values] = $this->extractFromFields($order, $blueprint);

        return array_merge((new OrderResource($order))->resolve()['data'], [
            'values' => $values,
            'itemActions' => Action::for($order, $context),
        ]);
    }
}
