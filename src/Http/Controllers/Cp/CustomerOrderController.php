<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class CustomerOrderController extends CpController
{
    public function index(Request $request)
    {
        return Order::where('customer_id', $request->customer)
            ->with('orderStatus')
            ->get()
            ->map(function ($order) {
                return array_merge($order->toArray(), [
                    'edit_url' => $order->editUrl(),
                    'delete_url' => $order->deleteUrl(),
                ]);
            });
    }
}
