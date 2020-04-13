<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Support\Facades\Event;
use Statamic\Http\Controllers\CP\CpController;

class UpdateOrderStatusController extends CpController
{
    public function update(Order $order, OrderStatus $status)
    {
        $this->authorize('update', Order::class);

        $order->update(['order_status_id' => $status->id]);

        Event::dispatch(new OrderStatusUpdated($order, $order->orderStatus));

        return redirect(cp_route('orders.index'))->with('success', "Set as $status->name.");
    }
}
