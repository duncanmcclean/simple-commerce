<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Order;
use Statamic\Http\Controllers\CP\CpController;

class OrderSearchController extends CpController
{
    public function __invoke()
    {
        $query = request()->input('search');

        if (! $query) {
            $results = Order::all();
        } else {
            $results = Order::all()
                ->filter(function ($item) use ($query) {
                    return false !== stristr((string) $item['title'], $query);
                });
        }

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('orders.search'),
                'sortColumn' => 'title',
            ]
        ]);
    }
}
