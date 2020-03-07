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

        $refund = (new $order->gateway_data['gateway'])->refund($order->gateway_data);

        if ($refund != true) {
            return back()
                ->with('error', $refund);
        }

        event(new OrderRefunded($order));

        return back()
            ->with('success', 'Order has been refunded.');
    }
}
