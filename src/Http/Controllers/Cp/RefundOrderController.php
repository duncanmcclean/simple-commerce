<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\StripeGateway;
use Statamic\Http\Controllers\CP\CpController;

class RefundOrderController extends CpController
{
    public function store(Order $order)
    {
        $this->authorize('refund', $order);

        if (! $order->payment_intent) {
            return back()->with('error', 'Refund failed because there is no PaymentIntent.');
        }

        (new StripeGateway())->issueRefund($order->payment_intent);

        event(new OrderRefunded($order));

        return back()->with('success', 'Order has been refunded.');
    }
}
