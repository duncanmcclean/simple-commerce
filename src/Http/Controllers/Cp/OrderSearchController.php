<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Models\Order;
use Statamic\Http\Controllers\CP\CpController;

class OrderSearchController extends CpController
{
    public function __invoke()
    {
        $query = request()->input('search');

        if (! $query) {
            $results = Order::all()
                ->map(function ($order) {
                    return array_merge($order->toArray(), [
                        'edit_url' => cp_route('orders.edit', ['order' => $order['uid']]),
                        'delete_url' => cp_route('orders.destroy', ['order' => $order['uid']]),
                    ]);
                });

            return $this->returnResponse($results);
        }

        $results = Order::all()
            ->filter(function ($item) use ($query) {
                return false !== stristr((string) $item['slug'], $query);
            })
            ->map(function ($order) {
                return array_merge($order->toArray(), [
                    'edit_url' => cp_route('orders.edit', ['order' => $order['uid']]),
                    'delete_url' => cp_route('orders.destroy', ['order' => $order['uid']]),
                ]);
            });

        return $this->returnResponse($results);
    }

    public function returnResponse($results)
    {
        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('orders.search'),
                'sortColumn' => 'title',
            ],
        ]);
    }
}
