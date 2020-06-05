<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Support\Facades\Event;
use Statamic\Http\Controllers\CP\CpController;

class RefundOrderController extends CpController
{
    public function store(Order $order)
    {
        $this->authorize('refund', $order);

        $transaction = (new $order->transactions[0]());

        if ($order->getHasBeenRefundedAttribute()) {
            return back()->with('error', 'Order has already been refunded.');
        }

        $refund = (new $transaction['gateway']())->refund($transaction);

        $transaction->update($refund);

        Event::dispatch(new OrderRefunded($order));

        return back()->with('success', 'Order has been refunded.');
    }
}
