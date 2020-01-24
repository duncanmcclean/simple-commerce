<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\Http\Controllers\CP\CpController;

class OrderStatusController extends CpController
{
    public function update(Order $order, OrderStatus $status)
    {
        $this->authorize('update', Order::class);

        $order->order_status_id = $status->id;
        $order->save();

        return redirect(cp_route('orders.index'))
            ->with('success', 'Set as '.$status->name.'.');
    }
}
