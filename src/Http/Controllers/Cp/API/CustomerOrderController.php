<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class CustomerOrderController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('update', $request->customer);

        return Order::with('orderStatus')
            ->where('customer_id', $request->customer)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Order $order) {
                return array_merge($order->toArray(), [
                    'edit_url' => $order->editUrl(),
                    'delete_url' => $order->deleteUrl(),
                ]);
            });
    }
}
