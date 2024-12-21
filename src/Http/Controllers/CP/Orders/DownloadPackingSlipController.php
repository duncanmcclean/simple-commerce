<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class DownloadPackingSlipController extends CpController
{
    public function __invoke(Request $request, $order)
    {
        $order = Order::find($order);

        $this->authorize('edit', $order);

        return view('simple-commerce::packing-slip', [
            'config' => config()->all(),
            'order' => $order,
        ]);
    }
}
