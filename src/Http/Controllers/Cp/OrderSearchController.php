<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Facades\Damcclean\Commerce\Models\Order;
use Statamic\Http\Controllers\CP\CpController;

class OrderSearchController extends CpController
{
    public function __invoke()
    {
        $results = Order::search(request()->input('search'));

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('orders.search'),
                'sortColumn' => 'status',
            ]
        ]);
    }
}
