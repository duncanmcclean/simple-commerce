<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\Http\Controllers\CP\CpController;

class UpdateOrderStatusController extends CpController
{
    public function __invoke(Order $order, OrderStatus $status)
    {
        $this->authorize('update', Order::class);

        $order->order_status_id = $status->id;
        $order->save();

        event(new OrderStatusUpdated($order, $order->customer));

        return redirect(cp_route('orders.index'))
            ->with('success', "Set as $status->name.");
    }
}
